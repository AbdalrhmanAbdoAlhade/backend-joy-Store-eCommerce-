<?php
// app/Http/Controllers/Api/V1/Admin/PaymentGatewayController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        return response()->json([
            'cod' => [
                'enabled' => Setting::get('payment_cod_enabled', true),
                'label' => 'الدفع عند الاستلام',
            ],
            'paymob' => [
                'enabled' => Setting::get('payment_paymob_enabled', false),
                'api_key' => Setting::get('paymob_api_key', ''),
                'integration_id' => Setting::get('paymob_integration_id', ''),
                'iframe_id' => Setting::get('paymob_iframe_id', ''),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'cod_enabled' => ['boolean'],
            'paymob_enabled' => ['boolean'],
            'paymob_api_key' => ['nullable', 'string'],
            'paymob_integration_id' => ['nullable', 'string'],
            'paymob_iframe_id' => ['nullable', 'string'],
        ]);

        if (isset($data['cod_enabled'])) {
            Setting::set('payment_cod_enabled', $data['cod_enabled'] ? '1' : '0', 'boolean', 'payment');
        }
        if (isset($data['paymob_enabled'])) {
            Setting::set('payment_paymob_enabled', $data['paymob_enabled'] ? '1' : '0', 'boolean', 'payment');
        }
        if (isset($data['paymob_api_key'])) {
            Setting::set('paymob_api_key', $data['paymob_api_key'], 'string', 'payment');
        }
        if (isset($data['paymob_integration_id'])) {
            Setting::set('paymob_integration_id', $data['paymob_integration_id'], 'string', 'payment');
        }
        if (isset($data['paymob_iframe_id'])) {
            Setting::set('paymob_iframe_id', $data['paymob_iframe_id'], 'string', 'payment');
        }

        return response()->json(['message' => 'تم تحديث إعدادات الدفع']);
    }
}
