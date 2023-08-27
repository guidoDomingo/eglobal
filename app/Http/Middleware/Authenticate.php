<?php namespace App\Http\Middleware;

use Closure;

class Authenticate
{


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $information = [];
        $error = [];
        $warning = [];
        $success = [];

        if (\Session::has('information'))
            $information = array_unique(array_merge_recursive($information, $this->getArray(\Session::get('information'))));
        if (\Session::has('error'))
            $error = array_unique(array_merge_recursive($error, $this->getArray(\Session::get('error'), 'error')));
        if (\Session::has('warning'))
            $warning = array_unique(array_merge_recursive($warning, $this->getArray(\Session::get('warning'), 'warning')));
        if (\Session::has('success'))
            $success = array_unique(array_merge_recursive($success, $this->getArray(\Session::get('success'), 'success')));

        $messages = [
            'error'       => $error,
            'information' => $information,
            'warning'     => $warning,
            'success'     => $success
        ];

        if (!$request->is('login'))
            try {
                if (!\Sentinel::check()) {
                    if ($request->ajax()) {
                        return response('Unauthorized.', 401);
                    } else {

                        $messages['information'][] = 'Por favor, inicie sesion.';

                        return redirect()->guest('/login')
                            ->with($messages);
                    }
                }

            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                return redirect()->guest('/login');
            }

        $request->session()->flash('messages', $messages);
        return $next($request);
    }

    protected function getArray($value, $type = 'information')
    {
        if (empty($value))
            return [];

        if (is_array($value))
            return $value;

        return [$value];
    }

}
