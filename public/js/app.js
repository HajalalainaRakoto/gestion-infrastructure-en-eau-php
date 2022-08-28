const btnToggler = document.querySelector('.btn-toggler')
const sidebar = document.querySelector('.sidebar')
const content = document.querySelector('.content')
const littleLine = document.querySelectorAll('.little-line')

btnToggler.addEventListener('click', () => {
  sidebar.classList.toggle('show')
  content.classList.toggle('slide')
  littleLine.forEach((line) => {
    line.classList.toggle('clicked')
  })
})