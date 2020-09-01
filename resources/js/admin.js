jQuery(document).ready(function () {
    if (document.querySelector('.updated.blue-ocean a.insights-data-we-collect'))
        document.querySelector('.updated.blue-ocean a.insights-data-we-collect').addEventListener('click', e => {
            let d = document.querySelector('.updated.blue-ocean .description');
            if (d.style.display === 'none') {
                d.style.display = 'flex';
            } else {
                d.style.display = 'none';
            }
        });
});