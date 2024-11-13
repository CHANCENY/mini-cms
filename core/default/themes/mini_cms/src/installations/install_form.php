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