<?php

namespace App\Http\Controllers;

use App\Helpers\Api;
use App\Models\Student;
use App\Models\StudentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function postRegister(Request $request) {

        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:student',
            'name' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return Api::restError($validator->errors());
        }

        $new = new Student();
        $new->name = $request->get('name');
        $new->phone = $request->get('phone');
        $new->password = Hash::make($request->get('password'));
        $new->school = null;
        $new->photo = null;
        $new->description = null;
        $new->save();
        unset($new->password,$new->created_at,$new->updated_at);
        return Api::restSuccess("Berhasil Mendaftar",$new);
    }

    public function postLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return Api::restError($validator->errors());
        }

        $student = Student::firstWhere('phone',$request->get('phone'));
        if ($student) {
            if (Hash::check($request->get('password'),$student->password)) {
                $student->photo = ($student->photo?asset($student->photo):null);
                unset($student->password,$student->created_at,$student->updated_at);
                return Api::restSuccess("Berhasil Mendaftar",$student);
            } else{
                return Api::restError('Password tidak sesuai');
            }
        }
        return Api::restError('User Not found');
    }

    protected function getFileName($file)
    {
        return Str::random(32) . '.' . $file->extension();
    }

    public function postUpdateProfile(Request $request) {

        $validator = Validator::make($request->all(), [
            'id_user' => 'required',
            'name' => '',
            'school' => '',
            'description' => '',
            'photo' => '',
        ]);

        if ($validator->fails()) {
            return Api::restError($validator->errors());
        }

        $new = Student::find($request->get('id_user'));
        if ($request->get('name')) {
            $new->name = $request->get('name');
        }
        if ($request->get('school')) {
            $new->school = $request->get('school');
        }
        if ($request->photo) {
            $filename = $this->getFileName($request->photo);
            $request->photo->move(base_path('public'), $filename);
            $new->photo = $filename;
        }
        if ($request->get('description')) {
            $new->description = $request->get('description');
        }
        $new->save();
        $new->photo = ($new->photo?asset($new->photo):null);

        unset($new->password,$new->created_at,$new->updated_at);
        return Api::restSuccess("Berhasil Mengupdate",$new);
    }

    public function postLike(Request $request) {
        $find = DB::table('student_like')
        ->where('users_id',$request->get('users_id'))
        ->where('user_id_like',$request->get('user_id_i_like'))
        ->first();

        $save = new StudentLike();
        if ($find) {
            $save = StudentLike::find($find->id);
        }
        $save->users_id = $request->get('users_id');
        $save->user_id_like = $request->get('user_id_i_like');
        $save->is_like = ($save->is_like?false:true);
        $save->save();

        $res['status'] = 200;
        $res['message'] = 'Berhasil';
        $res['liked'] = ($save->is_like == 1?true:false);
        return response()->json($res);
    }

    public function isILike($request,$data){
        $id = [];
        foreach ($data as $row) {
            $id[] = $row->id;
        }
        $list = StudentLike::query()
            ->where('users_id',$request->get('users_id'))
            ->where('is_like',1)
            ->whereIn('user_id_like',$id)
            ->get();

        $result = collect($list);
        $result = $result->groupBy('user_id_like');
        return $result->toArray();
    }

    public function getListFriend(Request $request) {
        $site = asset('');
        $list = Student::query()
            ->where('student.id','!=',$request->get('users_id'))
            ->leftjoin(
                DB::raw("(SELECT student_like.user_id_like,count(student_like.id) as total_like
                                FROM student_like where is_like = 1 GROUP BY student_like.user_id_like) as total_like"),
                "total_like.user_id_like","=","student.id")
            ->select('id','name','phone','school',DB::raw("concat('$site',student.photo) as photo"),'description','total_like')
            ->get();

        $like = self::isILike($request,$list);

        foreach ($list as $key => $value) {
            if (array_key_exists($value->id,$like)) {
                $value->like_by_you = true;
            }else{
                $value->like_by_you = false;
            }
        }

        return Api::restSuccess("OK",$list);
    }
}
