<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    { 
        $variantsArray['Color'] = ProductVariant::where('variant_id', 1)->distinct('variant')->pluck('variant');
        $variantsArray['Size'] = ProductVariant::where('variant_id', 2)->distinct('variant')->pluck('variant');
        $variantsArray['Style'] = ProductVariant::where('variant_id', 6)->distinct('variant')->pluck('variant');
        
        $searchData = $request->all();
        if(isset($searchData['price_from']) && $searchData['price_from'] == "0") {
            $searchData['price_from'] = 1;
        }
        // dd(array_filter($searchData));

        if(count(array_filter($searchData)) == 5) {
            $data = $request->all();
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $products = Product::whereDate('created_at', $date)->where('title', 'LIKE', "%$request->title%")->get();
            $result = [];
            foreach ($products as $product) {
                $variant_id = ProductVariant::where('variant', $request->variant)->where('product_id', $product->id)->first()->id;       
                // $results = $product->product_variant_prices()->where('price', '>=', $request->price_from)->where('price', '<=', $request->price_to)->where('product_variant_one', $variant_id)->orWhere('product_variant_two', $variant_id)->orWhere('product_variant_three', $variant_id)->get();

                $product->product_variant_prices = $product->product_variant_prices()->where('price', '>=', $request->price_from)->where('price', '<=', $request->price_to)->where('product_variant_one', $variant_id)->orWhere('product_variant_two', $variant_id)->orWhere('product_variant_three', $variant_id)->get();
                // $finalResult = [];
                // foreach($results as $result) {
                //     if($result->product_variant_one == $variant_id) {
                //         array_push($finalResult, $result);
                //     } else if($result->product_variant_two == $variant_id) {
                //         array_push($finalResult, $result);
                //     } else if($result->product_variant_three == $variant_id) {
                //         array_push($finalResult, $result);
                //     }
                // }
                // $product->variants = $finalResult;
                
            }
            $totalProducts = $products->count();
            return view('products.index', ['products' => $products, 'totalProducts' => $totalProducts, 'noReq' => false, 'variantsArray' => $variantsArray, 'data' => $data]);
            dd($products);
            
        }
        $products = Product::paginate(5);
        $startingFrom = '';
        if($products->currentPage() < 2) {
            $startingFrom = 1;
            $endingAt = $startingFrom + $products->count() - 1;
        } else {
            $startingFrom = $products->currentPage() * 5 - 4;
            $endingAt = $startingFrom + $products->count() - 1;
        }
        // dd($startingFrom);
        $totalProducts = Product::count();

        // dd($products[0]->product_variant_prices[0]->product_variant_three_details);
        return view('products.index', ['products' => $products, 'startingFrom' => $startingFrom, 'endingAt' => $endingAt, 'totalProducts' => $totalProducts, 'noReq' => true, 'variantsArray' => $variantsArray]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {

            $product = Product::create([
                'title' => $request->product_name,
                'sku' => $request->product_sku,
                'description' => $request->description
            ]);

            foreach ($request->product_variant as $variants) {
                // dd($variants['value']);
                foreach($variants['value'] as $index => $value) {
                    // dd($value);
                    $productVariant = ProductVariant::create([
                        'variant' => strtolower($value),
                        'variant_id' => $variants['option'],
                        'product_id' => $product->id,
                    ]);
                }
            }

            foreach($request->product_preview as $variantWithPrice) {
                // dd($variantWithPrice);
                $variants = explode('/', $variantWithPrice['variant']);
                $variants = array_filter($variants);
                $product_variants = [];
                foreach($variants as $variant) {
                    $product_variant_id = ProductVariant::where('variant', strtolower($variant))->where('product_id', $product->id)->first()->id;
                    array_push($product_variants, $product_variant_id);
                }

                $productVariantPrice = ProductVariantPrice::create([
                    'product_variant_one' => $product_variants[0] ?? null,
                    'product_variant_two' => $product_variants[1] ?? null,
                    'product_variant_three' => $product_variants[2] ?? null,
                    'product_id' =>$product->id,
                    'price' => $variantWithPrice['price'] ?? '',
                    'stock' => $variantWithPrice['stock'] ?? ''
                ]);
            } 

            return redirect()->route('product.index')->withMessage('Successfully created');
            dd($variants);

        } catch (\Exception $e) {
            return redirect()->back()->withValue()->withErrors($e->getMessage());
            // dd($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        // dd($product->product_variants);
        $color = ProductVariant::where('variant_id', 1)->where('product_id', $product->id)->distinct()->pluck('variant');
        $size = ProductVariant::where('variant_id', 2)->where('product_id', $product->id)->distinct()->pluck('variant');
        $style = ProductVariant::where('variant_id', 6)->where('product_id', $product->id)->distinct()->pluck('variant');

        if($color->count() > 0) {
            $variantsForProduct[1] = $color;
        }
        if($size->count() > 0) {
            $variantsForProduct[2] = $size;
        }
        if($style->count() > 0) {
            $variantsForProduct[6] = $style;
        }

        // dd($variantsForProduct);

        $product = $product;
        $variants = Variant::all();
        return view('products.edit', compact('variants', 'product', 'variantsForProduct'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        // dd($request->all());
        $product->title = $request->product_name;
        $product->sku = $request->product_sku;
        $product->description = $request->product_description;

        $product->update();

        foreach($request->product_preview as $preview)
        {
            $productVariantPrice = ProductVariantPrice::find($preview['id']);
            $variants = explode(' / ', $preview['variant']);
            // dd($variants);
            $variantsArray = [];
            foreach($variants as $index => $variant) {
                $productVariant = ProductVariant::where('variant', $variant)->where('product_id', $product->id)->first();
                $variantsArray[$index] = $productVariant->id;
            }
            // dd($variantsArray);
            // dd($productVariantPrice);
            $productVariantPrice->update([
                'product_variant_one' => $variantsArray[0] ?? null,
                'product_variant_two' => $variantsArray[1] ?? null,
                'product_variant_three' => $variantsArray[2] ?? null,
                'product_id' => $product['id'],
                'price' => $preview['price'] ?? null,
                'stock' => $preview['stock'] ?? null

            ]);
        }
        return redirect()->route('product.index')->withMessage("Product updated successfully");

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function fileUpload(Request $request)
    {
        return response()->json("check");
    }
}
