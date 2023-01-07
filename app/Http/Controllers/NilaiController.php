<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    /**
     * Create a new controller instance
     *
     * - Registers the "auth:api" middleware, which verifies that the user is authenticated before allowing access to the controller's methods.
     * 
     * @return  void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Retrive all Nilai records including students name, jurusan name, dosen name, and course name.
     * Also returning the age of students calculated by tanggal_lahir field on "Mahasiswa" table
     *
     * @return  \Illuminate\Http\JsonResponse  all the data requested.
     */
    public function showAllNilai()
    {
        // Join all the record needed
        $nilai = Nilai::join('mahasiswa', 'data_nilai.nim', '=', 'mahasiswa.nim')
            ->join('jurusan', 'mahasiswa.jurusan', '=', 'jurusan.id')
            ->join('mata_kuliah', 'data_nilai.matkul_id', '=', 'mata_kuliah.id')
            ->join('dosen', 'data_nilai.dosen_id', '=', 'dosen.id');

        // Get all data by joined record
        $data = $nilai->get([
            'data_nilai.nim',
            'mahasiswa.nama as nama',
            DB::raw("YEAR(CURRENT_DATE) - YEAR(mahasiswa.tl) - (DATE_FORMAT(CURRENT_DATE, '%m%d')<DATE_FORMAT(mahasiswa.tl, '%m%d')) as umur"),
            'data_nilai.nilai',
            'dosen.nama as dosen',
            'mata_kuliah.judul as matkul',
            'jurusan.nama as jurusan',
            'data_nilai.keterangan'
        ]);

        // Return JSON Response
        return response()->json([
            'status' => 200,
            'title' => 'success',
            'data' => $data
        ]);
    }

    public function addNilai(Request $request)
    {
        // Check if the user is Dosen
        if (auth()->user()->role_id !== 2) :
            return response()->json([
                'message' => "Tidak diperbolehkan!"
            ], 401);
        endif;

        // Perform Validation
        $this->validate(
            $request,
            [
                'nim'        => 'required',
                'matkul_id'  => [
                    'required',
                    Rule::unique('data_nilai')->where(function ($query) use ($request) {
                        return $query
                            ->whereNim($request->nim)
                            ->whereMatkulId($request->matkul_id);
                    }),
                    'exists:mata_kuliah,id'
                ],
                'dosen_id' => 'required',
                'nilai' => 'required|integer|between:0,101',
            ],
            [
                'matkul_id.unique' => "Data nilai Sudah ada!"
            ]
        );

        Nilai::create($request->all());
        $nilai = Nilai::firstOrCreate($request->all());

        return response()->json([
            'status' => '200',
            'title' => 'success',
            'description' => 'Sukses menambahkan!'
        ], 201);
    }

    /**
     * Update nilai records for spesific students
     *
     * @param   Request  $request  Fields user want to update
     * @param   int   $nim      is student's nim
     * @param   int   $matkul   is course id
     *
     * @return  JsonResponse return success if any row affected
     */
    public function editNilai(Request $request, $nim, $matkul)
    {
        // Check if the user is Dosen
        if (auth()->user()->role_id !== 2) :
            return response()->json([
                'message' => "Tidak diperbolehkan!"
            ], 401);
        endif;

        // Querying the data and update nilai record
        $nilai = Nilai::where('nim', '=', $nim)->where('matkul_id', '=', $matkul)->firstOrFail();
        $nilai->update($request->all());

        // Return Json reponse
        return response()->json([
            'data' => $nilai,
            'status' => '200',
            'title' => 'success',
            'description' => 'Sukses diubah!'
        ], 200);
    }

    /**
     * Delete a Nilai record.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @param string $nim The NIM of the student
     * @param int $matkul The ID of the matkul
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function deleteNilai(Request $request, $nim, $matkul)
    {
        // Check if the user is Dosen
        if (auth()->user()->role_id !== 2) :
            return response()->json([
                'message' => "Tidak diperbolehkan!"
            ], 401);
        endif;

        // Retrive Nilai record and Delete
        $nilai = Nilai::where('nim', '=', $nim)->where('matkul_id', '=', $matkul)->firstOrFail();
        $nilai->delete();

        // Return response
        return response('Terhapus', 200);
    }

    /**
     * Calculate the average of Nilai for each students.
     * Grouped by the students name and give students name.
     *
     * @return  \Illuminate\Http\JsonResponse  JSON Response containing average nilai of each students.
     */
    public function avgNilai()
    {
        // Temporarily disable strict mode for the database connection.
        // This allows us to use the AVG() function without having to group by all non-aggregate columns.
        DB::statement("SET SQL_MODE=''");

        // Use the Nilai model to retrieve the nim, average nilai, and nama fields
        // from the data_nilai and mahasiswa tables, respectively.
        // Group the results by the nim field.
        $nilai = Nilai::select(db::raw("data_nilai.nim, avg(data_nilai.nilai) as rerata, mahasiswa.nama"))
            ->join('mahasiswa', 'mahasiswa.nim', '=', 'data_nilai.nim')
            ->groupBy('nim');

        // Return the results in a JSON response.
        return response()->json(
            [
                'status' => '200',
                'title' => 'success',
                'data' => $nilai->get()
            ]
        );
    }

    /**
     * Upload and import Nilai records from a CSV file.
     *
     * @param Request $request The HTTP request object.
     *
     * @throws Exception If the uploaded file is invalid or an error occurs while importing the records.
     *
     * @return Response A JSON response indicating the status of the operation.
     * - On success: HTTP status code 200 and a success message.
     * - On error: HTTP status code 401 (unauthorized) if the user is not a Dosen, or HTTP status code 500 (internal server error) if an exception is thrown.
     */
    public function uploadNilai(Request $request)
    {
        if (auth()->user()->role_id !== 2) :
            return response()->json([
                'message' => "Tidak diperbolehkan!"
            ], 401);
        endif;

        $file = $request->file('uploaded_file');
        if ($file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize(); //Get size of uploaded file in bytes
            //Check for file extension and size
            $this->checkUploadedFileProperties($extension, $fileSize);
            //Where uploaded file will be stored on the server 
            $location = 'uploads'; //Created an "uploads" folder for that
            // Upload file
            $file->move($location, $filename);
            // In case the uploaded file path is to be stored in the database 

            $filepath = public_path($location . "/" . $filename);
            // Reading file
            $file = fopen($filepath, "r");
            $importData_arr = array(); // Read through the file and store the contents as an array
            $i = 0;
            //Read the contents of the uploaded file 
            while (($filedata = fgetcsv($file, 1000, ";")) !== FALSE) {
                $num = count($filedata);
                // Skip first row (Remove below comment if you want to skip the first row)
                if ($i == 0) {
                    $i++;
                    continue;
                }
                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = $filedata[$c];
                }
                $i++;
            }
            fclose($file); //Close after reading
            $j = 0;
            foreach ($importData_arr as $importData) {
                $j++;
                $data = [
                    'nim' => intval($importData[0]),
                    'matkul_id' => intval($importData[1]),
                    'dosen_id' => intval($importData[2]),
                    'nilai' => intval($importData[3]),
                    'keterangan' => $importData[4]
                ];
                try {
                    if (!(Nilai::where('nim', $data['nim'])->where('matkul_id', $data['matkul_id'])->first())) :
                        Nilai::create($data);
                    else :
                        throw new \InvalidArgumentException("Data suda ada!");
                    endif;
                } catch (\Exception $e) {
                    //throw $th;
                    throw new Exception('Data sudah ada!');
                }
            }
            return response()->json([
                'status' => 201,
                'title' => 'success',
                'description' => "$j data sudah ditambahkan!"
            ], 201);
        } else {
            //no file was uploaded
            throw new \Exception('No file was uploaded', Response::HTTP_BAD_REQUEST);
        }
    }

    public function checkUploadedFileProperties($extension, $fileSize)
    {
        $valid_extension = array("csv"); //Only want csv and excel files
        $maxFileSize = 2097152; // Uploaded file size limit is 2mb
        if (in_array(strtolower($extension), $valid_extension)) {
            if ($fileSize <= $maxFileSize) {
            } else {
                throw new \Exception('No file was uploaded', Response::HTTP_REQUEST_ENTITY_TOO_LARGE); //413 error
            }
        } else {
            throw new \Exception('Invalid file extension', Response::HTTP_UNSUPPORTED_MEDIA_TYPE); //415 error
        }
    }

    /**
     * Calculate the average of Nilai for each students.
     * Grouped by the students name and give students name.
     *
     * @return  \Illuminate\Http\JsonResponse  JSON Response containing average nilai of each students.
     */
    public function avgByJurusan()
    {
        // Temporarily disable strict mode for the database connection.
        // This allows us to use the AVG() function without having to group by all non-aggregate columns.
        DB::statement("SET SQL_MODE=''");

        // Use the Nilai model to retrieve the nim, average nilai, and nama fields
        // from the data_nilai and mahasiswa tables, respectively.
        // Join with jurusan table to get the jurusan name.
        // Group the results by the jurusan_id field.
        $nilai = Nilai::select(DB::raw("jurusan.nama as jurusan, avg(data_nilai.nilai) as rerata"))
            ->join('mahasiswa', 'mahasiswa.nim', '=', 'data_nilai.nim')
            ->join('jurusan', 'jurusan.id', '=', 'mahasiswa.jurusan')
            ->groupBy('jurusan.id');

        // Return Json response.
        return response()->json([
            'data' => $nilai->get(),
            'status' => '200',
            'title' => 'success'
        ]);
    }
}
