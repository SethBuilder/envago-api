<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
            'whatsapp_number' => $request->whatsapp_number,
            'bank_details'    => !empty($bankDetails) ? $bankDetails : $provider->bank_details,
        ]);

        return back()->with('success', 'Configurações salvas.');
    }
}
