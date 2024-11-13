let country_field = document.getElementsByClassName('country');
if(country_field) {
    Array.from(country_field).forEach((country)=>{
        let other_fields_wrapper = country.parentElement.nextElementSibling;
        country.addEventListener('input',(e)=>{
            const country_code = e.target.value;
            const name = country.name.substring(0, country.name.indexOf("___"));
            const countryXhr = new XMLHttpRequest();
            countryXhr.open('GET', '/assets/address/auto/'+country_code+'/'+name,true);
            countryXhr.onload = function () {
                if(this.status === 200) {
                    if(other_fields_wrapper) {
                        other_fields_wrapper.innerHTML = this.responseText;

                    }
                }
            }
            countryXhr.send();
        })
    });
}