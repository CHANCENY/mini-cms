<?php

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once "../vendor/autoload.php";

$request = \Mini\Cms\Controller\Request::createFromGlobals();

if($request->getMethod() == 'POST') {
    $payload = $request->getPayload();
    $config = Services::create('config.factory');

    // Check if all good
    if($config instanceof ConfigFactory) {
        $database = [
            'db_host' => $payload->get('db_host'),
            'db_user' => $payload->get('db_user'),
            'db_password' => $payload->get('db_password'),
            'db_name' => $payload->get('db_name'),
            'db_type' => $payload->get('db_type'),

        ];
        $config->set('database', $database);
        if($config->save(true)) {
            (new RedirectResponse('/site-configuration',StatusCode::PERMANENT_REDIRECT->value))->send();
            exit;
        }
    }

}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<section class="container w-75 mt-lg-5 p-lg-5">
    <div class="col-md-9 border rounded p-5 bg-light">
        <div class="d-block mt-lg-5 mb-lg-5">
            <h2>Database Configuration</h2>
        </div>
        <div class="d-block">
            <form class="form" method="POST">
                <div class="form-group">
                    <label for="db-type">Database Type:</label>
                    <select id="db-type" class="form-control" name="db_type">
                        <option>--Select Database Type--</option>
                        <option value="sqlite">SQLITE</option>
                        <option value="mysql">MYSQL</option>
                    </select>
                </div>
                <div class="border rounded p-3 mt-lg-5" id="fields">

                </div>
            </form>
        </div>
        <div class="d-block mt-lg-5">
            <div class="d-block mt-lg-5 mb-lg-5">
                <h4>Installation Instructions:</h4>
            </div>
            <div class="d-block">
                <ol>
                    <li>
                        <strong>Configs directory</strong><br>
                        <p>
                            Before you begin the installation process, ensure that the "configs" folder has the necessary permissions to be written to. This means that the folder should allow for changes to be made to its contents.
                        </p>
                    </li>
                    <li>
                        <strong>Configurations.json file:</strong><br>
                        <p>
                            Additionally, verify that you have a file named "configurations.json" present in the designated location, and ensure that this file also has the appropriate permissions to be written to. This ensures that the installation process can successfully update or modify the configurations stored within this file.
                        </p>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    const dbType = document.getElementById('db-type');
    if(dbType) {
        dbType.addEventListener('change',(e)=>{
            if(e.target.value === 'mysql') {
                mysql();
            }else {
                sqlite();
            }

            const submit = document.createElement('button');
            submit.type = 'submit';
            submit.className = 'btn btn-secondary d-block mt-5';
            submit.textContent = 'Save Database Configuration'

            document.getElementById('fields').appendChild(submit)
        });
    }

    function mysql() {
        const fields = [
            {
                field_type: 'text',
                id: 'db-host',
                name: 'db_host',
                label: 'Database Host:',
            },
            {
                field_type: 'text',
                id: 'db-name',
                name: 'db_name',
                label: 'Database Name:',
            },
            {
                field_type: 'text',
                id: 'db-user',
                name: 'db_user',
                label: 'Database User:',
            },
            {
                field_type: 'text',
                id: 'db-password',
                label: 'Database Password:',
                name: 'db_password',
            }
        ];
        const wrapper = document.getElementById('fields');

        if(wrapper) {
            wrapper.innerHTML = '';
            fields.forEach((field)=>{
                const fieldTag = document.createElement('input');
                fieldTag.type = field.fieid_type;
                fieldTag.id = field.id;
                fieldTag.name = field.name;
                fieldTag.className = 'form-control mb-2';

                const label = document.createElement('label');
                label.for = field.id;
                label.textContent = field.label;

                const div = document.createElement('div');
                div.className = 'form-group';
                div.appendChild(label);
                div.appendChild(fieldTag);
                wrapper.appendChild(div);
            })
        }
    }

    function sqlite() {
        const fields = [
            {
                field_type: 'text',
                id: 'db-name',
                name: 'db_name',
                label: 'Database File Name:',
            }
        ];
        const wrapper = document.getElementById('fields');

        if(wrapper) {
            wrapper.innerHTML = '';
            fields.forEach((field)=>{
                const fieldTag = document.createElement('input');
                fieldTag.type = field.fieid_type;
                fieldTag.id = field.id;
                fieldTag.name = field.name;
                fieldTag.className = 'form-control mb-2';

                const label = document.createElement('label');
                label.for = field.id;
                label.textContent = field.label;

                const div = document.createElement('div');
                div.className = 'form-group';
                div.appendChild(label);
                div.appendChild(fieldTag);
                wrapper.appendChild(div);
            })
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
