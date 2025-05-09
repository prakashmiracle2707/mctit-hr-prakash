<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\ClientDocument;
use App\Models\Utility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ClientController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Employee')) {
            $clients = Client::where('created_by', Auth::user()->creatorId())->get();
            return view('client.index', compact('clients'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function create()
    {
        if (Auth::user()->can('Create Employee')) {
            $documents = Document::where('created_by', Auth::user()->creatorId())->get();
            return view('client.create', compact('documents'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Employee')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|unique:users',
                'phone' => 'required',
                'address' => 'required',
                'password' => 'required|min:6', // Add password validation for user
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->with('error', $validator->messages()->first());
            }

            // 1. Create User first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => 'client', // or 'employee' if that's your intended role
                'lang' => app()->getLocale(),
                'created_by' => Auth::user()->creatorId(),
                'email_verified_at' => now(),
            ]);

            // 2. Create Client linked to the user
            $client = new Client();
            $client->user_id = $user->id;
            $client->name = $request->name;
            $client->email = $request->email;
            $client->phone = $request->phone;
            $client->password = Hash::make($request->password);
            $client->address = $request->address;
            $client->created_by = Auth::user()->creatorId();
            $client->save();

            // 3. Handle Document Uploads
            if ($request->hasFile('document')) {
                foreach ($request->document as $key => $doc) {
                    $file = $request->file('document')[$key];
                    $fileName = time() . "_" . $file->getClientOriginalName();
                    $dir = 'uploads/client_documents/';
                    $path = Utility::upload_coustom_file($request, 'document', $fileName, $dir, $key, []);
                    if ($path['flag'] != 1) {
                        return redirect()->back()->with('error', __($path['msg']));
                    }

                    ClientDocument::create([
                        'client_id' => $client->id,
                        'document_id' => $key,
                        'document_value' => $fileName,
                        'created_by' => Auth::user()->creatorId(),
                    ]);
                }
            }

            return redirect()->route('client.index')->with('success', __('Client successfully created.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function edit($id)
    {
        if (Auth::user()->can('Edit Employee')) {
            $id = Crypt::decrypt($id);
            $client = Client::find($id);
            $documents = Document::where('created_by', Auth::user()->creatorId())->get();

            return view('client.edit', compact('client', 'documents'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('Edit Employee')) {
            $client = Client::find($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:clients,email,' . $client->id,
                'phone' => 'required',
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->with('error', $validator->messages()->first());
            }

            $client->name = $request->name;
            $client->email = $request->email;
            $client->phone = $request->phone;
            $client->address = $request->address;
            $client->company_name = $request->company_name;
            $client->save();

            if ($request->hasFile('document')) {
                foreach ($request->document as $key => $doc) {
                    $fileName = time() . "_" . $doc->getClientOriginalName();
                    $dir = 'uploads/client_documents/';
                    $path = Utility::upload_coustom_file($request, 'document', $fileName, $dir, $key, []);
                    if ($path['flag'] != 1) {
                        return redirect()->back()->with('error', __($path['msg']));
                    }

                    $existing = ClientDocument::where('client_id', $client->id)->where('document_id', $key)->first();
                    if ($existing) {
                        $existing->document_value = $fileName;
                        $existing->save();
                    } else {
                        ClientDocument::create([
                            'client_id' => $client->id,
                            'document_id' => $key,
                            'document_value' => $fileName,
                            'created_by' => Auth::user()->creatorId(),
                        ]);
                    }
                }
            }

            return redirect()->route('client.index')->with('success', __('Client successfully updated.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function destroy($id)
    {
        if (Auth::user()->can('Delete Employee')) {
            $client = Client::findOrFail($id);
            $documents = ClientDocument::where('client_id', $client->id)->get();

            foreach ($documents as $doc) {
                $filePath = 'uploads/client_documents/' . $doc->document_value;
                if (\File::exists($filePath)) {
                    \File::delete($filePath);
                }
                $doc->delete();
            }

            $client->delete();

            return redirect()->route('client.index')->with('success', __('Client successfully deleted.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function show($id)
    {
        if (Auth::user()->can('Show Employee')) {
            $id = Crypt::decrypt($id);
            $client = Client::find($id);
            $documents = Document::where('created_by', Auth::user()->creatorId())->get();

            return view('client.show', compact('client', 'documents'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
