<?php

namespace App\Http\Controllers;

// use App\Author;
use App\Models\Author;
use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function showAllAuthors()
    {
        return response()->json(Author::all());
    }

    public function showOneAuthor($id)
    {
        return response()->json(Author::find($id));
    }

    public function create(Request $request)
    {
        $author = Author::create($request->all());

        return response()->json($author, 201);
    }

    public function update($id, Request $request)
    {
        $author = Author::findOrFail($id);
        $author->update($request->all());

        return response()->json($author, 200);
    }

    public function delete($id)
    {
        Author::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }

    public function test(Request $request)
    {

        // $data = DB::table('users')
        //     ->select('users.email', 'users.role_id', 'role.name')
        //     ->join('role', 'role.id', '=', 'users.role_id')
        //     ->get();

        $data = db::select(db::raw("SELECT * , YEAR(CURRENT_DATE) - YEAR(mahasiswa.tl) - (DATE_FORMAT(CURRENT_DATE, '%m%d')<DATE_FORMAT(mahasiswa.tl, '%m%d')) as umur FROM mahasiswa"));
        return response()->json($data);
    }
}
