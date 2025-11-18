<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendWebPush;

class AdminPushController extends Controller
{
    public function sendTest(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        dispatch(new SendWebPush(['title' => $data['title'], 'body' => $data['body'], 'url' => '/']));

        return redirect()->back()->with('success', 'Notification test envoyée (job dispatché).');
    }
}
