document.getElementById('alert-close').addEventListener('click', () => {
  document.getElementById('alert').style.opacity = 0;
  setTimeout(() => {
    document.getElementById('alert').style.display = 'none'
  }, 200)
})
