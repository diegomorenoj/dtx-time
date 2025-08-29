<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\UserCourse;
use Illuminate\Http\Request;

class UserCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = UserCourse::all();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List UserCourse Successfully'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            'course_id' => 'required',
            'user_id' => 'required',
            'hours' => 'required',
            'status' => 'required',
            'objective_id' => 'required',
        ];

        $messages = [
            'course_id.required' => 'El curso es obligatorio.',
            'user_id.required' => 'El usuario es obligatorio.',
            'hours.required' => 'Las horas son obligatorias.',
            'status.required' => 'El estado es obligatorio.',
            'objective_id.required' => 'El objetivo es obligatorio.'
        ];

        // VALIDACIONES
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, 500);
        }

        $userCourse = new UserCourse;
        $userCourse->course_id = $input['course_id'];
        $userCourse->user_id = $input['user_id'];
        $userCourse->attend_how = $input['attend_how'];
        $userCourse->progress = $input['progress'];
        $userCourse->qualification = $input['qualification'];
        $userCourse->hours = $input['hours'];        
        $userCourse->status = $input['status'];
        $userCourse->objective_id = $input['objective_id'];
        // GUARDAR
        $userCourse->save();

        $response = [
            'success' => true,
            'data' => $userCourse,
            'message' => 'UserCourse Stored Successfully'
        ];

        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserCourse  $userCourse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserCourse $userCourse)
    {
        $input = $request->all();

        $rules = [
            'course_id' => 'required',
            'user_id' => 'required',
            'hours' => 'required',
            'status' => 'required',
            'objective_id' => 'required',
        ];

        $messages = [
            'course_id.required' => 'El curso es obligatorio.',
            'user_id.required' => 'El usuario es obligatorio.',
            'hours.required' => 'Las horas son obligatorias.',
            'status.required' => 'El estado es obligatorio.',
            'objective_id.required' => 'El objetivo es obligatorio.'
        ];

        // VALIDACIONES
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, 500);
        }

        $userCourse->course_id = $input['course_id'];
        $userCourse->user_id = $input['user_id'];
        $userCourse->attend_how = $input['attend_how'];
        $userCourse->progress = $input['progress'];
        $userCourse->qualification = $input['qualification'];
        $userCourse->hours = $input['hours'];        
        $userCourse->status = $input['status'];
        $userCourse->objective_id = $input['objective_id'];
        // GUARDAR
        $userCourse->save();

        $response = [
            'success' => true,
            'data' => $userCourse,
            'message' => 'UserCourse Updated Successfully'
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserCourse  $userCourse
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserCourse $userCourse)
    {
        $userCourse->delete();
        $data = null;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'UserCourse Deleted Successfully.'
        ];

        return response()->json($response, 200);
    }
}
