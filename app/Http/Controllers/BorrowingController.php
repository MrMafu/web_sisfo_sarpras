<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowingResource;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ["id", "item.name", "user.username", "status", "due", "created_at", "updated_at"];
        $sort = $request->get("sort", "id");
        $direction = $request->get("direction", "asc") === "desc" ? "desc" : "asc";

        if (!in_array($sort, $allowedSorts)) {
            $sort = "id";
        }

        // Auto-reject pending overdue borrowings
        Borrowing::where("status", Borrowing::statusPending)
            ->where("due", "<", now())
            ->update(["status" => Borrowing::statusRejected]);
        
        // Auto-mark overdue borrowings and update item units
        $overdueBorrowings = Borrowing::with("borrowingDetails.itemUnit")
            ->where("status", Borrowing::statusApproved)
            ->where("due", "<", now())
            ->whereDoesntHave("returning", function ($query) {
                $query->where("status", "approved");
            })
            ->get();

        foreach ($overdueBorrowings as $borrowing) {
            $borrowing->update(["status" => Borrowing::statusOverdue]);

            foreach ($borrowing->borrowingDetails as $detail) {
                if ($detail->itemUnit->status === ItemUnit::statusBorrowed) {
                    $detail->itemUnit->update(["status" => ItemUnit::statusOverdue]);
                }
            }
        }

        $query = Borrowing::with(["user", "item", "approver", "borrowingDetails.itemUnit"]);
        if ($search = $request->get("search")) {
            $query->whereHas("item", fn($q) => $q->where("name", "like", "%{$search}%"));
        }

        if ($status = $request->get("status")) {
            $query->where("status", $status);
        }

        $query->select("borrowings.*");
        if ($sort === "item.name") {
            $query->join("items", "borrowings.item_id", "=", "items.id")
                ->orderBy("items.name", $direction);
        } elseif ($sort === "user.username") {
            $query->join("users", "borrowings.user_id", "=", "users.id")
                ->orderBy("users.username", $direction);
        } else {
            $query->orderBy("borrowings.{$sort}", $direction);
        }

        $borrowings = $query->paginate(10)
            ->appends($request->only(["search", "status", "sort", "direction"]));

        if ($request->ajax()) {
            return view("components.table", [
                "items"   => $borrowings,
                "headers" => [
                    "id"            => "ID",
                    "item.name"     => "Item",
                    "user.username" => "User",
                    "status"        => "Status",
                    "due"           => "Due Date",
                    "created_at"    => "Created",
                    "updated_at"    => "Updated",
                ],
                "sortField"     => $sort,
                "sortDirection" => $direction,
                "actions"       => true,
                "actionProps"   => ["showRoute"  => "borrowings.show"],
            ]);
        }

        if ($request->expectsJson()) {
            if (Auth::user()->role === "user") {
                $query->where("user_id", Auth::id());
            }
            $data = BorrowingResource::collection($query->get());
            return ApiResponse::success($data, "Borrowings retrieved successfully.");
        }

        return view("borrowings.index", [
            "borrowings" => $borrowings,
            "sort"       => $sort,
            "direction"  => $direction,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Method not allowed.", 405);
        }
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            abort(404);
        }

        if (Auth::user()->role !== "user") {
            return ApiResponse::error("Forbidden: only users can request borrowing.", 403);
        }

        $validator = Validator::make($request->all(), [
            "item_id"  => "required|exists:items,id",
            "quantity" => "required|integer|min:1",
            "due"      => "required|date|after:now",
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error.", 422, $validator->errors());
        }

        $borrowingData = $validator->validated();
        $availableUnits = ItemUnit::where("item_id", $borrowingData["item_id"])
            ->where("status", "available")
            ->count();

        if ($availableUnits < $borrowingData["quantity"]) {
            return ApiResponse::error(
                "Not enough available units. Requested {$borrowingData["quantity"]}, but only {$availableUnits} available."
            );
        }

        $borrowing = Borrowing::create([
            "user_id"  => Auth::id(),
            "item_id"  => $borrowingData["item_id"],
            "quantity" => $borrowingData["quantity"],
            "due"      => $borrowingData["due"],
        ]);

        $data = new BorrowingResource($borrowing);
        return ApiResponse::success($data, "Borrowing request created.", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $borrowing = Borrowing::with([
            "user", "item", "approver", "borrowingDetails.itemUnit", "returning.handler"
        ])->find($id);

        if (!$borrowing) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Borrowing not found.", 404);
            }
            abort(404);
        }

        // Auto-reject if overdue
        $this->autoRejectOverduePending($borrowing);

        // Auto-mark as overdue
        $this->autoMarkOverdueUnits($borrowing);

        $data = new BorrowingResource($borrowing);
        if ($request->expectsJson()) {
            if (Auth::user()->role === "user" && $borrowing->user_id !== Auth::id()) {
                return ApiResponse::error("Forbidden.", 403);
            }
            return ApiResponse::success($data, "Borrowing details retrieved.");
        }

        return view("borrowings.show", compact("borrowing"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        if ($request->expectsJson()) {
            return ApiResponse::error("Method not allowed.", 405);
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $borrowing = Borrowing::find($id);
        if (!$borrowing) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Borrowing not found.", 404);
            }
            abort(404);
        }

        if (Auth::user()->role !== "admin") {
            if ($request->expectsJson()) {
                return ApiResponse::error("Forbidden.", 403);
            }
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            "status" => "required|in:approved,rejected"
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return ApiResponse::error("Validation error.", 422, $validator->errors());
            }
            return back()->withErrors($validator, "edit")->withInput();
        }

        $newStatus = $request->status;
        DB::beginTransaction();
        try {
            $borrowing->status = $newStatus;
            if ($newStatus === "approved") {
                $borrowing->approved_at = now();
                $borrowing->approved_by = Auth::id();
                $borrowing->save();

                $units = ItemUnit::where("item_id", $borrowing->item_id)
                    ->where("status", "available")
                    ->orderBy("sku", "asc")
                    ->lockForUpdate()
                    ->take($borrowing->quantity)
                    ->get();
                
                if ($units->count() < $borrowing->quantity) {
                    DB::rollBack();
                    
                    $msg = "Not enough available units.";
                    if ($request->expectsJson()) {
                        return ApiResponse::error($msg);
                    } else {
                        return back()->withErrors(["quantity" => $msg]);
                    }
                }

                foreach ($units as $unit) {
                    $unit->update(["status" => ItemUnit::statusBorrowed]);
                    BorrowingDetail::create([
                        "borrowing_id" => $borrowing->id,
                        "item_unit_id" => $unit->id,
                    ]);
                }

            } else if ($newStatus === "rejected") {
                $borrowing->update();
            } else if ($newStatus === "returned") {
                $borrowing->update();
            }

            DB::commit();

            $borrowing->load(["user", "item", "approver", "borrowingDetails.itemUnit", "returning"]);
            $data = new BorrowingResource($borrowing);

            if ($request->expectsJson()) {
                return ApiResponse::success($data, "Borrowing updated successfully.");
            }

            return redirect()->route("borrowings.show", $borrowing->id)
                ->with("status", "Borrowing updated successfully.");

        } catch (\Throwable $throw) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return ApiResponse::error("Failed to update borrowing.", 500, $throw->getMessage());
            }

            return back()->withErrors(["error" => "Failed to update borrowing."]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->expectsJson()) {
            abort(404);
        }

        if (Auth::user()->role !== "user") {
            return ApiResponse::error("Forbidden: only users can cancel.", 403);
        }

        $borrowing = Borrowing::find($id);
        if (!$borrowing) {
            return ApiResponse::error("Borrowing not found.", 404);
        }

        if ($borrowing->status !== "pending") {
            return ApiResponse::error("Only pending borrowings can be cancelled.");
        }

        $borrowing->delete();
        return ApiResponse::noContent();
    }

    public function exportExcel()
    {
        $borrowings = Borrowing::with(["user", "item", "approver"])->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            "A" => ["ID", 10],
            "B" => ["Item", 15],
            "C" => ["User", 15],
            "D" => ["Quantity", 10],
            "E" => ["Status", 15],
            "F" => ["Due Date", 20],
            "G" => ["Approved At", 20],
            "H" => ["Approved By", 15],
            "I" => ["Created At", 20],
            "J" => ["Updated At", 20]
        ];

        foreach ($headers as $col => [$title, $width]) {
            $sheet->setCellValue($col."1", $title);
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $row = 2;
        foreach ($borrowings as $borrowing) {
            $due = $borrowing->due ? \Carbon\Carbon::parse($borrowing->due) : null;
            $approvedAt = $borrowing->approved_at ? \Carbon\Carbon::parse($borrowing->approved_at) : null;
            $createdAt = $borrowing->created_at ? \Carbon\Carbon::parse($borrowing->created_at) : null;
            $updatedAt = $borrowing->updated_at ? \Carbon\Carbon::parse($borrowing->updated_at) : null;

            $sheet->setCellValue("A".$row, $borrowing->id);
            $sheet->setCellValue("B".$row, $borrowing->item->name);
            $sheet->setCellValue("C".$row, $borrowing->user->username);
            $sheet->setCellValue("D".$row, $borrowing->quantity);
            $sheet->setCellValue("E".$row, $borrowing->status);
            $sheet->setCellValue("F".$row, $due);
            $sheet->setCellValue("G".$row, $approvedAt ?: "N/A");
            $sheet->setCellValue("H".$row, $borrowing->approver?->username ?: "N/A");
            $sheet->setCellValue("I".$row, $createdAt);
            $sheet->setCellValue("J".$row, $updatedAt);
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle("A1:J$lastRow")->applyFromArray([
            "borders" => [
                "allBorders" => [
                    "borderStyle" => Border::BORDER_THIN,
                    "color" => ["argb" => "FF000000"],
                ],
            ],
        ]);

        $writer = new Xlsx($spreadsheet);
        $fileName = "borrowings_export_" . now()->format("Ymd_His") . ".xlsx";

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save("php://output");
            },
            $fileName
        );
    }

    // Auto-reject pending borrowings if overdue
    protected function autoRejectOverduePending(Borrowing $borrowing)
    {
        if ($borrowing->status === "pending" && $borrowing->due < now()) {
            $borrowing->update(["status" => "rejected"]);
            return true;
        }
        return false;
    }

    // Auto-mark item units overdue
    protected function autoMarkOverdueUnits(Borrowing $borrowing)
    {
        if ($borrowing->status === Borrowing::statusApproved && $borrowing->due < now() &&
            (!$borrowing->returning || $borrowing->returning->status !== "approved")) {
            
            $borrowing->update(["status" => Borrowing::statusOverdue]);
            foreach ($borrowing->borrowingDetails as $detail) {
                if ($detail->itemUnit->status !== ItemUnit::statusOverdue &&
                    $detail->itemUnit->status !== ItemUnit::statusLost) {
                    $detail->itemUnit->update(["status" => ItemUnit::statusOverdue]);
                }
            }
            return true;
        }
        return false;
    }
}