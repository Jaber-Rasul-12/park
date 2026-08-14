<?php

namespace Jaber\Player\Http;

use Illuminate\Routing\Controller;
use Jaber\Player\Models\Invoice;
use Jaber\Player\Models\Price;
use Jaber\Player\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class InvoiceApiController extends Controller
{
    /**
     * API Key ثابت
     */
    protected $apiKey = 'YOUR_SECRET_API_KEY_123456789';

    /**
     * التحقق من API Key
     */
    private function validateApiKey(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide X-API-Key header or api_key parameter.'
            ], 401);
        }

        if ($apiKey !== $this->apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key.'
            ], 401);
        }

        return null;
    }

    /**
     * Display a listing of invoices.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // التحقق من المفتاح
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $invoices = Invoice::with(['product', 'price'])->get();
            
            return response()->json([
                'success' => true,
                'data' => $invoices
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // التحقق من المفتاح
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:jaber_player_products,id',
                'price_id' => 'required|exists:jaber_player_prices,id',
                'number' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // التحقق: هل يوجد فاتورة لنفس المنتج اليوم؟
            $today = Carbon::today();
            $existingInvoice = Invoice::where('product_id', $request->product_id)
                ->whereDate('created_at', $today)
                ->first();

            if ($existingInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already exists for this product today.',
                    'data' => [
                        'existing_invoice' => $existingInvoice,
                        'product_id' => $request->product_id,
                        'date' => $today->toDateString()
                    ]
                ], 409); // 409 Conflict
            }

            // Create the invoice
            $invoice = Invoice::create([
                'product_id' => $request->product_id,
                'price_id' => $request->price_id,
                'number' => $request->number,
            ]);

            // Calculate total price
            $price = Price::find($request->price_id);
            $totalPrice = $price ? $price->price * $request->number : 0;

            // Load relationships for the response
            $invoice->load(['product', 'price']);

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $invoice,
                'total_price' => $totalPrice
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        // التحقق من المفتاح
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $invoice = Invoice::with(['product', 'price'])->find($id);
            
            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            $totalPrice = $invoice->price ? $invoice->price->price * $invoice->number : 0;

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'total_price' => $totalPrice
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // التحقق من المفتاح
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $invoice = Invoice::find($id);
            
            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'product_id' => 'sometimes|exists:jaber_player_products,id',
                'price_id' => 'sometimes|exists:jaber_player_prices,id',
                'number' => 'sometimes|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // إذا تم تغيير product_id، تحقق من عدم وجود فاتورة أخرى لنفس المنتج اليوم
            if ($request->has('product_id') && $request->product_id != $invoice->product_id) {
                $today = Carbon::today();
                $existingInvoice = Invoice::where('product_id', $request->product_id)
                    ->whereDate('created_at', $today)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingInvoice) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another invoice already exists for this product today.',
                        'data' => [
                            'existing_invoice' => $existingInvoice,
                            'product_id' => $request->product_id,
                            'date' => $today->toDateString()
                        ]
                    ], 409);
                }
            }

            $invoice->update($request->all());
            $invoice->load(['product', 'price']);

            // Calculate new total price if fields are updated
            $totalPrice = $invoice->price ? $invoice->price->price * $invoice->number : 0;

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'data' => $invoice,
                'total_price' => $totalPrice
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        // التحقق من المفتاح
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $invoice = Invoice::find($id);
            
            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من وجود فاتورة اليوم لمنتج معين
     */
    public function checkToday(Request $request)
    {
        // التحقق من المفتاح
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:jaber_player_products,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $today = Carbon::today();
            $invoice = Invoice::where('product_id', $request->product_id)
                ->whereDate('created_at', $today)
                ->with(['product', 'price'])
                ->first();

            if ($invoice) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'message' => 'Invoice exists for today',
                    'data' => $invoice
                ], 200);
            }

            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'No invoice exists for today',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check invoice: ' . $e->getMessage()
            ], 500);
        }
    }
}