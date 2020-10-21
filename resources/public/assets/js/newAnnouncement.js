import 'symfony-collection/jquery.collection.js';

$(document).ready(() => {
    $('.form_collection').collection(
        {
            prototype_name: '__name__',
            allow_add: true,
            allow_remove: true,
            name_prefix: 'add_announcement_form[images]'
        }
    );
})
