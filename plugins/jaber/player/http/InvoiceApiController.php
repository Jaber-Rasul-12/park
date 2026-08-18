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
     * API Key ثابت - نفس المفتاح المستخدم في تطبيق Java
     */
    protected $apiKey = 'azadi_park_jaber_ali_12122121';

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
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:jaber_player_products,id',
                'price_id' => 'sometimes|exists:jaber_player_prices,id',
                'number' => 'required|numeric|min:1',
                'date' => 'sometimes|date', // يمكن إرسال تاريخ محدد
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // استخدام التاريخ المرسل أو تاريخ اليوم
            $invoiceDate = $request->input('date') 
                ? Carbon::parse($request->input('date'))->startOfDay()
                : Carbon::today();

            // التحقق: هل يوجد فاتورة لنفس المنتج في نفس التاريخ؟
            $existingInvoice = Invoice::where('product_id', $request->product_id)
                ->whereDate('date', $invoiceDate)
                ->first();

            if ($existingInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already exists for this product on this date.',
                    'data' => [
                        'existing_invoice' => $existingInvoice,
                        'product_id' => $request->product_id,
                        'date' => $invoiceDate->toDateString()
                    ]
                ], 409);
            }

            // الحصول على price_id المناسب
            $priceId = $request->input('price_id');
            
            // إذا لم يتم إرسال price_id، جلب أحدث سعر للمنتج
            if (empty($priceId)) {
                $latestPrice = Price::where('product_id', $request->product_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                if (!$latestPrice) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No price found for this product. Please set a price first.'
                    ], 422);
                }
                $priceId = $latestPrice->id;
            }

            // حساب total_price
            $price = Price::find($priceId);
            $totalPrice = $price ? $price->price * $request->number : 0;

            // Create the invoice مع إضافة التاريخ
            $invoice = Invoice::create([
                'product_id' => $request->product_id,
                'price_id' => $priceId,
                'number' => $request->number,
                'total_price' => $totalPrice,
                'date' => $invoiceDate, // استخدام التاريخ المحدد
            ]);

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
                'date' => 'sometimes|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // إذا تم تغيير product_id، تحقق من عدم وجود فاتورة أخرى لنفس المنتج في نفس التاريخ
            if ($request->has('product_id') && $request->product_id != $invoice->product_id) {
                $invoiceDate = $request->has('date') 
                    ? Carbon::parse($request->input('date'))->startOfDay()
                    : Carbon::parse($invoice->date)->startOfDay();
                    
                $existingInvoice = Invoice::where('product_id', $request->product_id)
                    ->whereDate('date', $invoiceDate)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingInvoice) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another invoice already exists for this product on this date.',
                        'data' => [
                            'existing_invoice' => $existingInvoice,
                            'product_id' => $request->product_id,
                            'date' => $invoiceDate->toDateString()
                        ]
                    ], 409);
                }
            }

            // تحديث الحقول
            $updateData = $request->all();
            
            // إذا تم تحديث السعر أو الكمية، قم بتحديث total_price
            if ($request->has('price_id') || $request->has('number')) {
                $priceId = $request->input('price_id', $invoice->price_id);
                $number = $request->input('number', $invoice->number);
                $price = Price::find($priceId);
                if ($price) {
                    $updateData['total_price'] = $price->price * $number;
                }
            }

            // إذا تم إرسال تاريخ، قم بتحويله
            if ($request->has('date')) {
                $updateData['date'] = Carbon::parse($request->input('date'))->startOfDay();
            }

            $invoice->update($updateData);
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
     * التحقق من وجود فاتورة في تاريخ محدد لمنتج معين
     */
    public function checkByDate(Request $request)
    {
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:jaber_player_products,id',
                'date' => 'sometimes|date', // إذا لم يتم إرسال التاريخ، يستخدم تاريخ اليوم
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $checkDate = $request->input('date') 
                ? Carbon::parse($request->input('date'))->startOfDay()
                : Carbon::today();

            $invoice = Invoice::where('product_id', $request->product_id)
                ->whereDate('date', $checkDate)
                ->with(['product', 'price'])
                ->first();

            if ($invoice) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'message' => 'Invoice exists for this date',
                    'data' => $invoice
                ], 200);
            }

            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'No invoice exists for this date',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من وجود فواتير في تاريخ محدد
     */
    public function checkInvoicesByDate(Request $request)
    {
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $checkDate = Carbon::parse($request->input('date'))->startOfDay();
            $invoices = Invoice::whereDate('date', $checkDate)
                ->with(['product', 'price'])
                ->get();

            return response()->json([
                'success' => true,
                'exists' => $invoices->count() > 0,
                'message' => $invoices->count() > 0 
                    ? 'Found ' . $invoices->count() . ' invoices for this date'
                    : 'No invoices found for this date',
                'date' => $checkDate->toDateString(),
                'count' => $invoices->count(),
                'data' => $invoices
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check invoices: ' . $e->getMessage()
            ], 500);
        }
    }
}