<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleAction = document.querySelector('.toggle-action');
    const toggleMenu = document.querySelector('.toggle-menu');

    const navAction = document.querySelector('.nav-action');
    const navMenu = document.querySelector('.nav-menu');

    toggleAction.addEventListener('click', function() {
        navAction.classList.toggle('active');
    });
    toggleMenu.addEventListener('click', function() {
        navMenu.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!navAction.contains(e.target) && !toggleAction.contains(e.target)) {
            navAction.classList.remove('active');
        }
        if (!navMenu.contains(e.target) && !toggleMenu.contains(e.target)) {
            navMenu.classList.remove('active');
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openModalButtons = document.querySelectorAll('#modal-open');
    const closeModalButtons = document.querySelectorAll('#modal-close');

    openModalButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = button.getAttribute('modal-data');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        });
    });

    closeModalButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });

    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openDropdownButtons = document.querySelectorAll('#dropdown-open');

    openDropdownButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            const dropdownId = button.getAttribute('dropdown-data');
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.classList.toggle('active');
            }
        });
    });

    document.querySelectorAll('.dropdown').forEach(function (dropdown) {
        dropdown.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown.active').forEach(function (dropdown) {
            dropdown.classList.remove('active');
        });
    });
});
</script>