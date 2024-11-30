```
// app/Http/Controllers/DecisionController.php
namespace App\Http\Controllers;

use App\Models\Decision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DecisionController extends Controller
{
    public function index()
    {
        $decisions = Decision::all();
        return view('decisions.index', compact('decisions'));
    }

    public function create()
    {
        $users = User::all();
        $dataPoints = DataPoint::all();
        return view('decisions.create', compact('users', 'dataPoints'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'data_point_id' => 'required|integer|exists:data_points,id',
            'decision' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $decision = new Decision();
        $decision->user_id = $request->input('user_id');
        $decision->data_point_id = $request->input('data_point_id');
        $decision->decision = $request->input('decision');
        $decision->timestamp = now();
        $decision->save();

        return redirect()->route('decisions.index')->with('success', 'Decision created successfully!');
    }
}
```

_Middleware:_

```
// app/Http/Middleware/Authenticate.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

// app/Http/Middleware/Authorize.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authorize
{
    public function handle($request, Closure $next, $permission)
    {
        if (!Auth::user()->hasPermission($permission)) {
            return redirect()->route('unauthorized');
        }

        return $next($request);
    }
}
```

_Routes:_

```
// routes/web.php
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/users', 'UserController@index')->name('users.index');
    Route::get('/users/create', 'UserController@create')->name('users.create');
    Route::post('/users', 'UserController@store')->name('users.store');

    Route::get('/data-sources', 'DataSourceController@index')->name('data-sources.index');
    Route::get('/data-sources/create', 'DataSourceController@create')->name('data-sources.create');
    Route::post('/data-sources', 'DataSourceController@store')->name('data-sources.store');

    Route::get('/data-points', 'DataPointController@index')->name('data-points.index');
    Route::get('/data-points/create', 'DataPointController@create')->name('data-points.create');
    Route::post('/data-points', 'DataPointController@store')->name('data-points.store');

    Route::get('/decisions', 'DecisionController@index')->name('decisions.index');
    Route::get('/decisions/create', 'DecisionController@create')->name('decisions.create');
    Route::post('/decisions', 'DecisionController@store')->name('decisions.store');
});

Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function () {
    Route::get('/users', 'AdminController@users')->name('admin.users');
    Route::get('/data-sources', 'AdminController@dataSources')->name('admin.data-sources');
    Route::get('/data-points', 'AdminController@dataPoints')->name('admin.data-points');
    Route::get('/decisions', 'AdminController@decisions')->name('admin.decisions');
});
```
