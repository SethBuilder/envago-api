<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function show()
    {
        $provider = auth()->user()->provider;
        return view('provider.settings', compact('provider'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'whatsapp_number'      => 'nullable|string|max:20',
            'bank_beneficiary'     => 'nullable|string|max:120',
            'bank_cpf_cnpj'        => 'nullable|string|max:20',
            'bank_name'            => 'nullable|string|max:80',
            'bank_address'         => 'nullable|string|max:160',
            'bank_city'            => 'nullable|string|max:80',
            'bank_state'           => 'nullable|string|max:2',
            'bank_postcode'        => 'nullable|string|max:10',
            'bank_account_type'    => 'nullable|in:corrente,poupanca',
            'bank_swift'           => 'nullable|string|max:20',
            'bank_branch'          => 'nullable|string|max:20',
            'bank_account'         => 'nullable|string|max:40',
        ]);

        $provider = auth()->user()->provider;

        $logoUrl = $provider->logo_url;
        if ($request->hasFile('logo')) {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            if ($provider->logo_url) {
                $oldPath = str_replace(Storage::disk($disk)->url(''), '', $provider->logo_url);
                Storage::disk($disk)->delete($oldPath);
            }
            $path    = $request->file('logo')->store('logos', ['disk' => $disk, 'visibility' => 'public']);
            $logoUrl = Storage::disk($disk)->url($path);
        }

        if ($request->input('type') === 'logo') {
            $provider->update(['logo_url' => $logoUrl]);
            return back()->with('success', 'Logo salvo.');
        }

        if ($request->input('type') === 'whatsapp') {
            $provider->update(['whatsapp_number' => $request->whatsapp_number]);
            return back()->with('success', 'WhatsApp salvo.');
        }

        $bankDetails = array_filter([
            'beneficiary'  => $request->bank_beneficiary,
            'cpf_cnpj'     => $request->bank_cpf_cnpj,
            'bank_name'    => $request->bank_name,
            'address'      => $request->bank_address,
            'city'         => $request->bank_city,
            'state'        => $request->bank_state ? strtoupper($request->bank_state) : null,
            'postcode'     => $request->bank_postcode,
            'account_type' => $request->bank_account_type,
            'swift'        => $request->bank_swift ? strtoupper($request->bank_swift) : null,
            'branch'       => $request->bank_branch,
            'account'      => $request->bank_account,
            'country'      => 'Brasil',
        ]);

        $provider->update([
            'logo_url'     => $logoUrl,
            'bank_details' => !empty($bankDetails) ? $bankDetails : $provider->bank_details,
        ]);

        return back()->with('success', 'Configurações salvas.');
    }
}
