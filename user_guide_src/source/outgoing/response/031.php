<?php

// Go back to the previous page.
return redirect()->back();

// Keep the old input values upon redirect so they can be used by the `old()` function.
return redirect()->back()->withInput();

// Set a flash message.
return redirect()->back()->with('foo', 'message');
