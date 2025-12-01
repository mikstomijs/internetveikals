
function cart() {
  const cart = document.getElementById('cart');
  if (!cart) return;


  const isVisible = cart.style.display !== 'none';
  if (isVisible) {
    cart.style.display = 'none';
    return;
  }

  const fav = document.getElementById('favorites');
  if (fav && fav.style.display !== 'none') fav.style.display = 'none';

  cart.style.display = 'block';

}




function account() {
  const account = document.getElementById('account');
  if (!account) return;

  const isVisible = account.style.display !== 'none';
  if (isVisible) {
    account.style.display = 'none';
    return;
  }
  account.style.display = 'flex';



}

function favorites() {
  const fav = document.getElementById('favorites');
  if (!fav) return;

  const isVisible = fav.style.display !== 'none';
  if (isVisible) {
    fav.style.display = 'none';
    return;
  }

  const cart = document.getElementById('cart');
  if (cart && cart.style.display !== 'none') cart.style.display = 'none';

  fav.style.display = 'block';

}





function mobileFilter() {
    const mobileFilter = document.getElementById('container_filter');
  if (!mobileFilter) return;

  const isVisible = mobileFilter.style.display !== 'block';
  if (isVisible) {
    mobileFilter.style.display = 'block';
    return;
  }

 mobileFilter.style.display = 'none';

}

