<?php
// app/Http/Controllers/Api/V1/TicketController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->isEmployee()
            ? Ticket::query()
            : $request->user()->tickets();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->with(['user:id,name', 'assignee:id,name'])
            ->latest()
            ->paginate(15);

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:5120'],
        ]);

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = $file->store('tickets', 'public');
            }
        }

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-' . strtoupper(Str::random(8)),
            'user_id' => $request->user()->id,
            'order_id' => $request->order_id,
            'subject' => $request->subject,
            'status' => \App\Enums\TicketStatus::Open,
            'priority' => $request->priority ?? 'medium',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'is_staff' => false,
            'attachments' => $attachmentPaths,
        ]);

        return response()->json([
            'message' => 'تم فتح التذكرة بنجاح',
            'ticket' => $ticket->load('messages'),
        ], 201);
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['messages.user:id,name,avatar', 'user:id,name', 'assignee:id,name', 'order']);

        return response()->json($ticket);
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $this->authorize('reply', $ticket);

        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:5120'],
        ]);

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = $file->store('tickets', 'public');
            }
        }

        $isStaff = $request->user()->isEmployee();

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'is_staff' => $isStaff,
            'attachments' => $attachmentPaths,
        ]);

        // تحديث حالة التذكرة
        if ($isStaff) {
            $ticket->update(['status' => \App\Enums\TicketStatus::WaitingCustomer]);
        } else {
            $ticket->update(['status' => \App\Enums\TicketStatus::InProgress]);
        }

        return response()->json([
            'message' => 'تم إرسال الرد',
            'ticket_message' => $message->load('user:id,name'),
        ], 201);
    }
}
