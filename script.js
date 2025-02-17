// Form validation
(function () {
  'use strict';

  // Get all the forms to validate
  var forms = document.querySelectorAll('.needs-validation');

  // Loop over the forms and prevent submission if not valid
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add('was-validated');
      }, false);
    });
})();

// Confirm delete action
function confirmDelete() {
    return confirm("Are you sure you want to delete this user?");
}

