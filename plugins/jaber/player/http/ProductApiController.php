<?php

namespace Jaber\Player\Http;

use Illuminate\Routing\Controller;
use Jaber\Player\Models\Product;
use Jaber\Player\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ProductApiController extends Controller
{
    /**
     * API Key ثابت (نفس المفتاح المستخدم في InvoiceApiController)
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
     * Display a listing of products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $products = Product::with(['prices'])->get();
            
            return response()->json([
                'success' => true,
                'data' => $products,
                'total' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product.
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
                'name' => 'required|string|max:255|unique:jaber_player_products,name',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the product
            $product = new Product();
            $product->name = $request->name;
            $product->description = $request->description ?? '';
            
            // Set the purgeable price value
            $product->setOriginalPurgeValue('price', $request->price);
            
            // Save the product (this will trigger beforeCreate and afterCreate)
            $product->save();

            // Load the prices relationship
            $product->load(['prices']);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
            
        } catch (\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
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
            $product = Product::with(['prices', 'invoices'])->find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product.
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
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255|unique:jaber_player_products,name,' . $id,
                'price' => 'sometimes|numeric|min:0',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update product fields
            if ($request->has('name')) {
                $product->name = $request->name;
            }
            if ($request->has('description')) {
                $product->description = $request->description;
            }

            // If price is being updated
            if ($request->has('price')) {
                // Create a new price record with the new price
                $product->prices()->create([
                    'price' => $request->price,
                    'status' => 1
                ]);
            }

            $product->save();
            $product->load(['prices']);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product.
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
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if product has invoices (the beforeDelete will handle this)
            try {
                $product->delete();
            } catch (\ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 409); // Conflict
            }

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product prices history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrices(Request $request, $id)
    {
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $prices = $product->prices()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $prices,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch prices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product invoices.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvoices(Request $request, $id)
    {
        $authCheck = $this->validateApiKey($request);
        if ($authCheck) {
            return $authCheck;
        }

        try {
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $invoices = $product->invoices()->with(['price'])->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name
                ],
                'total_invoices' => $invoices->count()
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoices: ' . $e->getMessage()
            ], 500);
        }
    }
}