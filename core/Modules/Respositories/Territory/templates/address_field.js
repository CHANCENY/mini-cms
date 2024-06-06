let country_field = document.getElementsByClassName('country');
if(country_field) {
    Array.from(country_field).forEach((country)=>{
        let other_fields_wrapper = country.parentElement.nextElementSibling;
        country.addEventListener('input',(e)=>{
            console.log(e.target.value);
            console.log(other_fields_wrapper)
        })
    });
}