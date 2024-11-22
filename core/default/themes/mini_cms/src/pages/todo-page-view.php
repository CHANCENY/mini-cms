<div class="h-100 bg-light rounded p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0">To Do List</h6>
        <a href="/todo/all">Show All</a>
    </div>
    <div class="d-flex mb-2">
        <input id="task" class="form-control bg-transparent" type="text" placeholder="Enter task">
        <input id="datetime" class="form-control bg-transparent" type="datetime-local" placeholder="Enter task">
        <button onclick="addTodoTask(this)" type="button" class="btn btn-primary ms-2">Add</button>
    </div>
    <div id="task-container">
        <?php $time_now = time(); if($content['tasks']): foreach ($content['tasks'] as $task): ?>
            <div class="d-flex align-items-center border-bottom py-2">
                <input value="<?= $task['tid'] ?? null; ?>" onclick="enableRemoveAll(this)" class="form-check-input m-0 task-checkbox" type="checkbox">
                <div class="w-100 ms-3">
                    <div class="d-flex w-100 align-items-center justify-content-between">
                        <span><?= $time_now < (int) $task['time_stamp'] ? ($task['task'] ?? null) : "<del>". ($task['task'] ?? null)."</del>"; ?></span>
                        <button onclick="removeTask(this)" class="btn btn-sm"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
    <div class="d-flex align-items-center py-2">
        <a id="remove-all" onclick="removeAll(this)" class="btn btn-primary d-none" href="">Remove All</a>
    </div>
</div>
<script>
    function addTodoTask(element) {
        const task = document.getElementById('task');
        const time = document.getElementById('datetime');
        if(time.value.length > 0 && task.value.length > 0) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/todo/block/actions/post',true);
            xhr.onload = function (){
                const task_cont = document.getElementById('task-container');
                const div = document.createElement('div');
                div.className = "d-flex align-items-center border-bottom py-2";
                div.innerHTML = JSON.parse(xhr.responseText).data;
                if(task_cont.childElementCount > 0) {
                    const topChild = task_cont.children[0];
                    task_cont.insertBefore(div,topChild);
                }else {
                    task_cont.appendChild(div)
                }
            }
            xhr.send(JSON.stringify({task: task.value, time: time.value}));
        }
    }

    function removeTask(element) {
        const topParent = element.parentElement.parentElement.parentElement;
        if(topParent) {
            const task_id = topParent.querySelector('input').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '/todo/block/actions/'+task_id+'/delete',true);
            xhr.onload = function () {
                if(this.status === 200) {
                    topParent.remove();
                }
            }
            xhr.send();
        }
    }

    function enableRemoveAll(element) {
        const checkboxes = document.querySelectorAll('.task-checkbox');
        const remove_all = document.getElementById('remove-all');
        if(checkboxes) {
            let flag = false;
           for (let i = 0; i < checkboxes.length; i++) {
               if(checkboxes[i].checked) {
                   flag = true;
               }
           }

           if(flag) {
               remove_all.classList.remove('d-none');
           }else {
               remove_all.classList.add('d-none')
           }
        }
    }

    const all_r = document.getElementById('remove-all');
    if(all_r) {
        all_r.addEventListener('click',(e)=>{
            e.preventDefault();
            removeAll();
        })
    }
    function removeAll() {
        const checkboxes = document.querySelectorAll('.task-checkbox');
        const values = Array.from(checkboxes).map((item)=>{
            if(item.checked) {
                return item.value;
            }
            return null;
        }).filter((item)=> item !== null);
        if(values.length > 0) {
            const xhr = new XMLHttpRequest();
            xhr.open('DELETE','/todo/block/actions/bulk/removal', true);
            xhr.onload = function () {
                if(this.status === 200) {
                    checkboxes.forEach((checkbox)=>{
                       if(checkbox.checked) {
                           checkbox.parentElement.remove();
                       }
                    })
                }
            }
            xhr.send(JSON.stringify({data:values}));
        }
    }
</script>