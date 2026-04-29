document.querySelectorAll('.qty-plus').forEach(btn => {
  btn.onclick = () => {
    let input = btn.parentNode.querySelector('.qty-input');
    input.value = parseInt(input.value) + 1;
  };
});

document.querySelectorAll('.qty-minus').forEach(btn => {
  btn.onclick = () => {
    let input = btn.parentNode.querySelector('.qty-input');
    if (input.value > 1) input.value--;
  };
});