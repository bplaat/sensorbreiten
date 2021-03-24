<?php

class View {
    public static function make(string $path, array $data = []): View {
        $view = new View();
        $view->path = $path;
        $view->data = $data;
        return $view;
    }

    public function with(string $key, $value): View {
        $this->data[$key] = $value;
        return $this;
    }

    public function render(): string {
        extract($this->data);
        ob_start();
        eval('?>' . preg_replace(
            [
                '/^\s*@session\((.*)\):/m',
                '/^\s*@error\((.*)\):/m',
                '/^\s*@auth:/m',

                '/^\s*@view\((.*)\)(.*)/m',
                '/^\s*@(.*)/m',
                '/{{(.*)}}/U',
                '/{!!(.*)!!}/U'
            ],
            [
                '<?php if (session($1) != null): ?>',
                '<?php if (hasError($1)): ?>',
                '<?php if (Auth::check()): ?>',

                '<?php echo view($1)$2->render() ?>',
                '<?php $1 ?>',
                '<?php echo htmlspecialchars($1, ENT_QUOTES, \'UTF-8\') ?>',
                '<?php echo $1 ?>'
            ],
            file_get_contents(ROOT . '/views/' . str_replace('.', '/', $this->path) . '.phtml')
        ));
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}

function csrf(): void {
    echo '<input type="hidden" name="_csrf_token" value="' . session('_csrf_token') . '">';
}

function view(string $path, array $data = []): View {
    return View::make($path, $data);
}

function abort(int $errorCode): View {
    http_response_code($errorCode);
    return view('errors.' . $errorCode);
}
