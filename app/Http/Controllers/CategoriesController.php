<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Category;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class CategoriesController extends Controller
{

    public function index(){
        $title = trans('app.categories');
        $categories = Category::all();

        return view('admin.categories', compact('title', 'categories'));
    }


    public function store(Request $request){

        $rules = [
            'category_name' => 'required'
        ];
        $this->validate($request, $rules);

        $slug = unique_slug($request->category_name, 'Category');
        $duplicate = Category::where('slug', $slug)->count();
        if ($duplicate > 0){
            return back()->with('error', trans('app.category_exists_in_db'));
        }

        $data = [
            'category_name' => $request->category_name,
            'slug' => $slug,
        ];

        Category::create($data);
        return back()->with('success', trans('app.category_created'));
    }

    public function edit($id){
        $category = Category::find($id);
        if (! $category){
            return trans('app.invalid_request');
        }
        $title = trans('app.edit_category');

        return view('admin.categories_edit', compact('title', 'category'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id){
        $category = Category::find($id);
        if (! $category){
            return trans('app.invalid_request');
        }

        $rules = [
            'category_name' => 'required'
        ];
        $this->validate($request, $rules);

        $data = [
            'category_name' => $request->category_name,
        ];

        $category->update($data);
        return back()->with('success', trans('app.category_updated'));
    }

    /**
     * @param Request $request
     * @return array
     *
     * Delete Category
     */
    public function destroy(Request $request){
        if(config('app.is_demo')) return ['success' => false];

        $category_id = $request->data_id;
        Category::find($category_id)->delete();
        return ['success' => 1];
    }

    /**
     * @param null $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * Find the single categories
     */
    public function singleCategory($slug = null){
        if ( ! $slug){
            abort(404);
        }
        $category = Category::whereSlug($slug)->first();
        $title = $category->category_name;
        return view('campaigns_by_category', compact('title', 'category'));
    }
    

}
