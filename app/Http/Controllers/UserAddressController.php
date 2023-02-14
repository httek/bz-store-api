<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $items = UserAddress::whereUserId($request->user()->id ?? 0)
            ->latest('defaults')
            ->latest()
            ->get();

        return success($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $this->validate($request, [
            'defaults' => 'in:0,1',
            'receiver' => 'required|string',
            'phone' => 'required|string|max:11|min:11',
            'region' => 'required|string',
            'detail' => 'required|string',
        ]);

        if (! Str::contains($validated['region'], ['北京', '北京市'])) {
            return fail('目前仅限北京地区');
        }

        $validated['user_id'] = $request->user()->id ?? 0;
        $item = UserAddress::create($validated);
        if ($validated['defaults'] ?? 0) {
            UserAddress::whereUserId($request->user()->id ?? 0)
                ->where('id', '!=', $item->id)
                ->update(['defaults' => 0]);
        }

        return success($item);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $where = ['id' => $id, 'user_id' => $request->user()->id ?? 0];
        $item = UserAddress::where($where)
            ->firstOrFail();

        return success($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $where = ['id' => $id, 'user_id' => $request->user()->id ?? 0];
        $item = UserAddress::where($where)
            ->firstOrFail();
        $validated = $this->validate($request, [
            'defaults' => 'in:0,1',
            'receiver' => 'string',
            'phone' => 'string|max:11|min:11',
            'region' => 'string',
            'detail' => 'string',
        ]);

        if (isset($validated['region']) && ! Str::contains($validated['region'], ['北京', '北京市'])) {
            return fail('目前仅限北京地区');
        }

        $item->update($validated);

        return success($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $where = ['id' => $id, 'user_id' => $request->user()->id ?? 0];
        $item = UserAddress::where($where)
            ->firstOrFail();
        $item->delete();

        return success();
    }
}
