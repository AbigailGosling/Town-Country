<?php

namespace App\Http\Controllers;

use App\Models\HealthMark;
use Illuminate\Http\Request;

class HealthMarkController extends Controller
{
    private static int $defaultPaginate = 25;

    /**
     * The default query to populate the list of HealthMarks on the HealthMark Index/Search page
     *
     * @return Builder
     */
    protected function baseQuery()
    {
        return HealthMark::query();
    }

    /**
     * Function which checks whether a SHA1 password is valid
     * @param string $plainPassword The password the HealthMark entered you wish to validate
     * @param string $hashedPassword The password that is hashed in the database
     * @return bool The result of the operation
     */
    protected function checkSha1Hash(string $plainPassword, string $hashedPassword)
    {
        return sha1($plainPassword) === $hashedPassword;
    }

    public function __construct()
    {
        $this->authorizeResource(HealthMark::class);
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
            'health-marks.index', [
                'health_marks' => $this->baseQuery()->paginate($this::$defaultPaginate),
                    //: $this->baseQuery()->where('disabled', false)->paginate($this::$defaultPaginate),
                        'search_term' => '',
                'show_disabled' => $showDisabled
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('health-marks/edit', ['health_mark' => new HealthMark, 'isNew' => true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $healthmark = HealthMark::create([
            'name' => $request->name,
        ]);
        return redirect()->route('health_marks.show',['health_mark'=>$healthmark]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $healthmark
     * @return \Illuminate\Http\Response
     */
    public function show(int $healthmark)
    {
        return view('health-marks/edit',
            ['health_mark' => HealthMark::findOrFail($healthmark),
            'isNew' => false
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $healthmark
     * @return \Illuminate\Http\Response
     */
    public function edit(int $healthmark)
    {
        return $this->show($healthmark);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $input = $request->all();
        $healthmark = HealthMark::find($id);
        $healthmark->name = $input['name'];
        $healthmark->disabled = array_key_exists("disabled", $input);
        $healthmark->save();
        $healthmark->touch();

        return redirect(route('health_marks.index'))->with(['message' => "Successfully updated $healthmark->name's account"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param HealthMark $healthmark
     * @return \Illuminate\Http\Response
     */
    public function destroy(HealthMark $healthmark)
    {
        //
    }

    /**
     * GET method to search HealthMarks in the system from the HealthMarks Index page
     * @param Request $request
     * @return View
     */
    public function search(Request $request)
    {
        $showDisabled = $request->input('showDisabled', false);
        $searchTerm = $request->get('search');
        $marksQB = $this->baseQuery()->where('name', 'like', '%' . $searchTerm . '%');
        if (!$showDisabled) $marksQB = $marksQB->where('disabled',false);
        return view(
            'health-marks.index', [
                'health_marks' => $marksQB->paginate($this::$defaultPaginate),
                'show_disabled' => true,
                'search_term' => $searchTerm
            ]
        );
    }
}
