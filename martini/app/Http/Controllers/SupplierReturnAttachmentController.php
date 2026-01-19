<?php

namespace App\Http\Controllers;

use App\Models\SupplierReturn;
use App\Models\SupplierReturnAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierReturnAttachmentController extends Controller
{
    /**
     * Store a newly created attachment with file upload support.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'   => 'required|integer',
            'return_id' => 'required|integer',
            'file'      => 'nullable|file',  // file upload
            'comments'  => 'nullable|string',
        ]);
        // Save file
        if ($request->hasFile('file')) {

            $file = FileController::PROCESS_ACTUAL_FILE($request->file('file'));

            $data['file_id'] = $file->id;
        }
        $data['product_collected'] = $request->exists('product_collected')?true:false;
        $sra =SupplierReturnAttachment::create($data);
        $sra->save();
        $supplierReturn = SupplierReturn::find($sra->return_id);
        return route("legacy",["path"=>'legacy/single_invoice_payments.php',"invoice_id"=>$supplierReturn->pick_id,"customer_id"=>$supplierReturn->supplier_id,"return"=>"y"]);
    }

    /**
     * Update an existing attachment, supports replacing file.
     */
    public function update(Request $request, SupplierReturnAttachment $supplierReturnAttachment)
    {
        $data = $request->validate([
            'user_id'   => 'required|integer',
            'return_id' => 'required|integer',
            'file'      => 'sometimes|nullable|file',
            'comments'  => 'required|nullable|string',
        ]);

        // Replace file if provided
        if ($request->hasFile('file')) {

            $file = FileController::PROCESS_ACTUAL_FILE($request->file('file'));

            $data['file_id'] = $file->id;
        }
        $supplierReturnAttachment->update($data);
        $supplierReturn = SupplierReturn::find($supplierReturnAttachment->return_id);
        return route("legacy",["path"=>'legacy/single_invoice_payments.php',"invoice_id"=>$supplierReturn->pick_id,"customer_id"=>$supplierReturn->supplier_id,"return"=>"y"]);
    }

    /**
     * Delete an attachment and its file.
     */
    public function destroy(SupplierReturnAttachment $supplierReturnAttachment)
    {
        $supplierReturnAttachment->delete();
        return redirect()->back();
    }
}
