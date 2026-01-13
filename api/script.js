function showSection(id) {
    document.querySelectorAll('.info-section').forEach(sec => sec.classList.remove('show'));
    document.getElementById(id).classList.add('show');

    document.querySelectorAll('.btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}
