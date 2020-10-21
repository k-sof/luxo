
import "@glidejs/glide/dist/css/glide.core.css"
import "@glidejs/glide/dist/css/glide.theme.css"
import "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import Glide from "@glidejs/glide";



const slides = document.querySelectorAll('.glide')
const conf = {
    type:'carousel',
    bound: true,
    gap: 50,
    breakpoints: {
        1024: {
            perView:3
        },
        760: {
            perView: 1
        },
        480: {
            perView:1
        }
    }
}

 slides.forEach(item => {
     new Glide(item, conf).mount()
 })


