<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends CommonController
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);  // Default to 10 users per page
        $search = $request->input('search', ''); // Get the search query from request
        $startDate = $request->input('start_date');  // Get start date from the request
        $endDate = $request->input('end_date');  // Get end date from the request
        
        $query = Tax::when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        
        if ($request->has('sort_by') && isset($request->sort_by) && $request->has('sort_direction')) {
            $query->orderBy($request->sort_by, $request->sort_direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $taxes = $query->paginate($perPage);
        return $this->sendResponse($taxes, "Fetched taxes successfully");
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'value' => 'required|string',
            'status' => 'required|boolean',
        ]); 
        
        $tax = Tax::create($validated);
                
        return $this->sendResponse($tax, "Created tax successfully");
    }

    public function show(Tax $tax)
    {
        return $this->sendResponse($tax, "Fetched tax successfully");
    }

    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'value' => 'required|string',
            'status' => 'required|boolean',
        ]);

        $tax->update($validated);
        return $this->sendResponse($tax, "Updated tax successfully");
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return $this->sendResponse('', "Deleted tax successfully");
    }
}
