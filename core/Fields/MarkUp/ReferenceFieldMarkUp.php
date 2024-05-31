<?php

namespace Mini\Cms\Fields\MarkUp;

use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Fields\FieldMarkUpInterface;

class ReferenceFieldMarkUp implements FieldMarkUpInterface
{

    private string $markup;

    private FieldInterface $field;

    public function buildMarkup(FieldInterface $field, array|null $default_value): FieldMarkUpInterface
    {
        $default_value = $default_value['value'] ?? null;
        $this->field = $field;
        $is_required = !empty($field->isRequired()) ? 'required' : null;
        $size = $this->field->getSize();
        $random = base64_encode(random_bytes(12));
        $this->markup = <<<FIELD_MARKUP
               <div class="form-group field-markup mt-3">
               <label for="field-{$this->field->getName()}">{$this->field->getLabel()}</label>
               <input type="text" name="{$this->field->getName()}" id="field-{$this->field->getName()}" class="form-control input-field-text"
                $is_required size="$size" value="{$default_value}">
               </div>
                
                <div class="d-none" style="border: 1px solid #eee;margin-top: 10px;padding: 5px;border-radius: 2px;">
                  <div id="filter-results-{$this->field->getName()}">
                  <ul></ul>
</div>
                </div>
                <script type="application/javascript" nonce="$random">
                document.getElementById('field-{$this->field->getName()}').addEventListener('input',(event)=> {
                  const data = {field: event.target.name, value: event.target.value};
                  setTimeout(()=>{
                       const parentEl = document.querySelector('#filter-results-{$this->field->getName()} > ul');
                      const xhr = new XMLHttpRequest();
                      xhr.open('GET', '/filters/autocomplete?'+ new URLSearchParams(data).toString(), true);
                      xhr.setRequestHeader('Content-Type', 'application/json');
                      xhr.onload = function () {
                          if(this.status === 200) {
                              try{
                                  parentEl.innerHTML = '';
                                  const data = JSON.parse(this.responseText);
                                  if(data.length > 0) {
                                      parentEl.parentElement.parentElement.classList.remove('d-none');
                                      parentEl.parentElement.parentElement.classList.add('d-block');
                                  }else {
                                      parentEl.parentElement.parentElement.classList.remove('d-block');
                                      parentEl.parentElement.parentElement.classList.add('d-none');
                                  }
                                  Array.from(data).forEach((item)=>{
                                      const li = document.createElement('li');
                                      li.style.listStyleType = 'none';
                                      const a = document.createElement('a');
                                      a.textContent = item.name;
                                      a.href = '#';
                                      a.title = item.id; 
                                      a.addEventListener('click',(e)=>{
                                          e.preventDefault();
                                          event.target.value = a.title;
                                          parentEl.parentElement.parentElement.classList.add('d-none');
                                      });
                                      li.appendChild(a);
                                      parentEl.appendChild(li);
                                  });
                              }catch(e) {
                                  
                              }
                          }
                          if(this.status === 417) {
                              parentEl.parentElement.parentElement.classList.remove('d-block');
                              parentEl.parentElement.parentElement.classList.add('d-none');
                          }
                      }
                      xhr.send();
                  }, 2000)  
                })
              
</script>
FIELD_MARKUP;
        return $this;
    }

    public function getMarkup(): string
    {
        return $this->markup;
    }

    public function setMarkup(string $markup): FieldMarkUpInterface
    {
        $this->markup = $markup;
    }

    public function getField(): FieldInterface
    {
        return $this->field;
    }

    public function __toString()
    {
        return $this->markup;
    }
}