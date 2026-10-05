<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AddAgent;
use App\Models\Enquiry;

class LeadController extends Controller
{
    //Function for all enquiries
    public function index() {
        //Total enquiry
        $total_enquiries = Enquiry::count();
        //New enquiry
        $new_leads = Enquiry::where('status', 'New')->count();
        //Follow up enquiry
        $follow_up = Enquiry::where('status', 'Follow Up')->count();
        //Converted enquiry
        $converted = Enquiry::where('status', 'Converted')->count();
        //Get enquiries
        $enquiries = Enquiry::orderBy('id', 'DESC')->paginate(20);
        //Get agents
        $agents = AddAgent::orderBy('name')->get();
        //Get agents counts
        $agentLeadCounts = Enquiry::selectRaw('lead_assigned_agent_id, COUNT(*) as total')
            ->whereNotNull('lead_assigned_agent_id')
            ->groupBy('lead_assigned_agent_id')
            ->pluck('total', 'lead_assigned_agent_id');

        return view('admin.leads.index', compact('enquiries','total_enquiries','new_leads','follow_up','converted','agents','agentLeadCounts'));
    }

    //Function for submit enquiry
    public function store(Request $request) {
        //Validate input fields
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'adult' => 'required|numeric|min:1',
            'child' => 'nullable|numeric|min:0',
            'destination_1' => 'required',
        ]);
        //Create enquiry
        Enquiry::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'adult' => $request->adult,
            'child' => $request->child ?? 0,
            'destination_1' => $request->destination_1,
            'destination_2' => $request->destination_2,
            'nights_1' => $request->nights_1,
            'nights_2' => $request->nights_2,
            'hotel_type' => $request->hotel_type,
            'food_type' => $request->food_type,
            'remark' => $request->remark,
            'timestamp' => now(),
            'date' => now()->toDateString(),
            'enquiry_no' => 'ENQ-' . date('Y') . '-' . str_pad(
                Enquiry::max('id') + 1,
                4,
                '0',
                STR_PAD_LEFT
            ),
            'ip' => $request->ip(),
            'status' => $request->status ?? 'New',
            'source' => $request->source ?? 'Manual',
            'lead_is_published' => $request->has('lead_is_published') ? 1 : 0,
            'lead_type' => $request->lead_type,
        ]);
        //Response
        return redirect()->route('admin.inquires')->with('success', 'Lead added successfully.');
    }
}


