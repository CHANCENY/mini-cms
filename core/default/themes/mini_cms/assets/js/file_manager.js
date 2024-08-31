
const fileInputTags = document.getElementsByTagName('input');
if(fileInputTags){
    Array.from(fileInputTags).forEach((field)=>{
        if(field.type === 'file') {
            // Event listener for file selection
            field.addEventListener('change', function() {
                progress_bar(field);
                const files = this.files;
                if (files.length > 0) {
                    // Create FormData object
                    const formData = new FormData();
                    for (let i = 0; i < files.length; i++) {
                        formData.append('files[]', files[i]);
                    }

                    // AJAX request to send files to PHP backend
                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress',(e)=>{
                        let percent = Math.floor((e.loaded / e.total) * 100);
                        const progress_bar = document.getElementById(field.id + '-progress');
                        if(progress_bar){
                            setTimeout(()=>{
                                progress_bar.value = Math.round(percent);
                            },1000);
                        }
                    },false);
                    xhr.open('POST', '/files/assets/uploader', true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const data = JSON.parse(xhr.responseText);
                            const ids = [];
                            data.forEach((item)=>{
                                const con = document.createElement('div');
                                con.className = "col px-5 ps-0 mt-1 mb-1";
                                const aTag = document.createElement('a');
                                aTag.href = "/"+ item.link;
                                aTag.textContent = item.name;
                                aTag.target = '_blank';
                                const span = document.createElement('span');
                                const f = field.name;
                                span.className = "remove float-end text-danger";
                                span.style.cursor = "pointer";
                                span.setAttribute('field',f);
                                span.title = 'remove';
                                span.ariaLabel = "remove";
                                span.textContent = 'x';
                                span.setAttribute('data',item.id);
                                span.addEventListener('click',(e)=>{
                                    remove_file(span);
                                    const list = field.value.split(',');
                                    const thisId = span.getAttribute('data');
                                    const filtered = list.filter((item)=> { return item !== thisId; });
                                    field.value = filtered.join(',');
                                    span.parentElement.remove();
                                    if(field.value.length === 0) {
                                        field.type = 'file';
                                        if(document.getElementById(field.id+'-progress')) {
                                            document.getElementById(field.id+'-progress').remove();
                                        }
                                    }
                                    console.log(field.value);
                                });
                                ids.push(item.id);

                                con.appendChild(aTag);
                                con.appendChild(span);

                                const parent = field.parentElement;
                                parent.appendChild(con);
                            });
                            field.type = 'hidden';
                            field.value = ids.join(',');
                        } else {
                            alert('Error uploading files.');
                        }
                    };
                    xhr.send(formData);
                }
            });
        }
    });

    const removeTags = document.getElementsByClassName('remove');
    if(removeTags) {
        Array.from(removeTags).forEach((item)=>{
            item.addEventListener('click',(e)=>{
                remove_file(item);
            })
        });
    }

    function remove_file(item) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '/files/assets/uploader?id='+item.getAttribute('data') + '&field='+item.getAttribute('field'), true);
        xhr.onload = function () {
            if(this.status === 200) {
                item.parentElement.remove();
            }
        }
        xhr.send();
    }

    function progress_bar(input_field) {
        const progress = document.createElement('progress');
        progress.value = 0;
        progress.max = 100;
        progress.id = input_field.id + '-progress';
        progress.setAttribute('style','width:100%');
        input_field.parentElement.appendChild(progress);
    }
}

