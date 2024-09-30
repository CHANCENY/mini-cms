class FormSettings {
    form_settings_field;
    forms
    constructor() {
        this.forms = document.querySelectorAll("form");
        this.form_settings_field = [];
        if(this.forms) {
            Array.from(this.forms).forEach((form)=>{
                const formSettings = form.querySelector("#form-settings-node-field");
                this.form_settings_field.push(JSON.parse(formSettings.value));
            })
        }
        this.handleFormEvents();
    }

    handleFormEvents() {
        for (let i = 0; i < this.form_settings_field.length; i++) {
            const settings = this.form_settings_field[i];
            for (let j = 0; j < settings.length; j++) {
                const field = settings[j];
                const key = Object.keys(field)[0];
                const field_doc = document.getElementById(key);
                const values = field[key]
                if(!values.is_null) {
                    field_doc.required = true
                }
                if(values.is_multiple && values.type !== 'file') {
                    const parentElDetails = field_doc.parentElement.parentElement;
                    const parentElDiv = field_doc.parentElement;
                    console.log(parentElDiv, parentElDetails)
                    const btn = document.createElement('a');
                    btn.className = "btn btn-secondary";
                    btn.textContent = "New +";
                    btn.addEventListener('click',(e)=>{
                        const cloned = parentElDiv.cloneNode(true);
                        let count = parentElDetails.getAttribute('limit_count') ? parseInt(parentElDetails.getAttribute('limit_count')) : 1;
                        if(count <= parseInt(values.limit))
                        {
                            parentElDetails.insertBefore(cloned,btn);
                        }
                       count++;
                        parentElDetails.setAttribute('limit_count', count.toString());
                    });
                    parentElDetails.appendChild(btn)
                }
            }
        }
    }
}
new FormSettings();