<?php
// app/Http/Controllers/Api/V1/Staff/TicketController.php
namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user:id,name', 'assignee:id,name'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->boolean('assigned_to_me')) {
            $query->where('assigned_to', $request->user()->id);
        }

        return response()->json($query->paginate(20));
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => ['required', 'in:open,in_progress,waiting_customer,resolved,closed'],
        ]);

        $ticket->update(['status' => $request->status]);

        return response()->json([
            'message' => 'تم تحديث حالة التذكرة',
            'ticket' => $ticket,
        ]);
    }

    public function assign(Request $request, Ticket $ticket)
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'status' => TicketStatus::InProgress,
        ]);

        return response()->json([
            'message' => 'تم تعيين التذكرة',
            'ticket' => $ticket->load('assignee:id,name'),
        ]);
    }
}
