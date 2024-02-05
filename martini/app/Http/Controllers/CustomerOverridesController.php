<?php

namespace App\Http\Controllers;

use App\Models\CommentLogging;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerOverridesController extends Controller
{
    private static int $defaultPaginate = 25;
    public function __construct()
    {
        $this->authorizeResource(User::class);
    }
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $showDisabled = $request->input('showDisabled', false);

        return view(
            'customers/overrides.index', [
                'customers' => $showDisabled ? Customer::paginate($this::$defaultPaginate)
                    : Customer::where('disabled', false)->paginate($this::$defaultPaginate),
                        'search_term' => '',
                'show_disabled' => $showDisabled
            ]
        );
    }
    
    public function edit(Customer $customer)
    {
        return view(
            'customers/overrides.edit',['customer' => $customer]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $override
     * @return \Illuminate\Http\Response
     */
    public function updateCredit(Request $request, int $override)
    {
        $customer = Customer::find($override);
        $request->validate([
            'credit_comment' => ['required', 'string'],
        ],
        [
            'credit_comment.required' => "Credit Checking Override: A comment is required to change.",
        ]);
        $customer->override != $customer->override;
        if ($customer->override == 0)$customer->override = 1;
        else $customer->override = 0;
        $customer->save();

        $cl = new CommentLogging();
        $cl->type = "credit_override";
        $cl->user_id = Auth::id();
        $cl->entity_id = $override;
        $cl->body = ($customer->override == 1)?"Enabled : ".$request->input('credit_comment'):"Disabled : ".$request->input('credit_comment');
        $cl->save();

        return redirect()->route('overrides.edit',[$customer->id]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $override
     * @return \Illuminate\Http\Response
     */
    public function updateDel(Request $request, int $override)
    {
        $customer = Customer::find($override);
        $request->validate([
            'del_comment' => ['required', 'string'],
        ],
        [
            'del_comment.required' => "Delivery Day Override: A comment is required to change.",
        ]);
        if ($customer->delivery_day_override == 0)$customer->delivery_day_override = 1;
        else $customer->delivery_day_override = 0;
        $customer->save();

        $cl = new CommentLogging();
        $cl->type = "delivery_override";
        $cl->user_id = Auth::id();
        $cl->entity_id = $override;
        $cl->body = ($customer->delivery_day_override == 1)?"Enabled : ".$request->input('del_comment'):"Disabled : ".$request->input('del_comment');
        $cl->save();

        return redirect()->route('overrides.edit',[$customer->id]);
    }

    /**
     * GET method to search users in the system from the Users Index page
     * @param Request $request
     * @return View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->get('search');
        return view(
            'customers/overrides.index', [
                'customers' => $this->fuzzyCustomerSearch($searchTerm,true)
                        ->paginate($this::$defaultPaginate)
                        ->appends(request()->query()),
                'show_disabled' => true,
                'search_term' => $searchTerm
            ]
        );
    }
    private function fuzzyCustomerSearch($name,$creditSearch=false,$disabledSearch=false)
	{
		global $mysqli;
		$thisUser = User::find(Auth::id());
        $users = prepareExecuteQuery("SELECT GROUP_CONCAT(`absent_id`) as `ids` FROM `active_holiday_cover` WHERE `cover_id` = ?",'i',[$thisUser->id])->fetch_assoc()['ids'];
		$users = ($users != "")?explode(",",$users):[];
		$users[] = $thisUser->id;		
		$users = implode(",",$users);
		$restrictionString = "";
		if ($thisUser->hasPermission("restrictedaccess")){
			$restrictionString = " `default_salesman_id` IN ($users) AND";
		}
		$name = $mysqli->real_escape_string($name);
		$tests = array(
			$name,
			str_replace(" ","",$name),
			str_replace(" & "," and ",$name),
			str_replace("&"," & ",$name)
		);
		$creditSearchControl = "";
		if ($creditSearch == false) $creditSearchControl ="AND (`credit_terms` > -1 || `credit_enabled` = 1)";
		$disabledSearchControl = "AND `disabled` <> '1'";
		if ($disabledSearch == true) $disabledSearchControl ="";
		$queries = array(
			"SELECT * FROM `customers` WHERE$restrictionString businessname LIKE '%%%s%%' $creditSearchControl $disabledSearchControl",
		);
		if (strlen($name)>2)
		{
			$queries[]="SELECT * FROM `customers` WHERE$restrictionString MATCH(businessname) AGAINST ('%s') $creditSearchControl $disabledSearchControl";
			$queries[]="SELECT * FROM `customers` WHERE$restrictionString businessnameDM LIKE CONCAT('%%',dm('%s'),'%%') $creditSearchControl $disabledSearchControl";
		}
		foreach ($tests as $test)
		{
			foreach ($queries as $query)
			{
				$x = sprintf($query,$test);			
				$y = Customer::whereRaw($x);
				$count = $y->get()->count();
				if ($count > 0 && $count < 20)
				{
					break 2;
				}
			}
		}
		return $y;
	}
}
