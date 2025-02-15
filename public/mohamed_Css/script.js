/*jshint esversion: 6 */
function confirmDeleting(stockId, url, token) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            // Créer le formulaire de suppression
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            // Ajouter le jeton CSRF au formulaire
            var csrfInput = document.createElement('input');
            csrfInput.setAttribute('type', 'hidden');
            csrfInput.setAttribute('name', '_token');
            csrfInput.setAttribute('value', token);
            form.appendChild(csrfInput);

            // Ajouter le formulaire à la page et le soumettre
            document.body.appendChild(form);
            form.submit();
            let fire = Swal.fire({
                title: "Deleted!",
                text: "Your file has been deleted.",
                icon: "success"
            });
        }
    });
}

function showSuccessMessage() {
    Swal.fire({
        title: "Good job!",
        text: "You clicked the button!",
        icon: "success"
    });
}

function showSuccessMessageEdit() {
    Swal.fire({
        position: "top-end",
        icon: "success",
        title: "Your work has been saved",
        showConfirmButton: false,
        timer: 1500
    });
}






