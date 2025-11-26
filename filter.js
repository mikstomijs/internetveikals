var products = document.querySelectorAll("a[name=product]");
var checkboxes = document.querySelectorAll("input[type=checkbox][name=filter]");
 products.forEach(function(product) {
      product.style.display = "block";
    });
let enabledSettings = [];


checkboxes.forEach(function(checkbox) {
  checkbox.addEventListener('change', function() {

    enabledSettings = Array.from(checkboxes)
      .filter(i => i.checked)
      .map(i => i.value);
    

    products.forEach(function(product) {
      const productCategory = product.getAttribute("value");
      if (enabledSettings.length === 0 || enabledSettings.includes(productCategory)) {
        product.style.display = "block";
      } else {
        product.style.display = "none";

      }
    });

  });
});






const sort = document.getElementById('sort');
const productContainer = document.querySelector(".container_products");
const productsArray = Array.from(document.querySelectorAll(".product-card"));
const originalProducts = Array.from(products); 

sort.addEventListener('change', function() {
    const sortOrder = this.value;
    let sortedProducts;

    if (sortOrder === "LtH") {
        sortedProducts = productsArray.sort(function(a, b) {
            return parseFloat(a.getAttribute('price')) - parseFloat(b.getAttribute('price'));
        });
    } else if (sortOrder === "HtL") {
        sortedProducts = productsArray.sort(function(a, b) {
            return parseFloat(b.getAttribute('price')) - parseFloat(a.getAttribute('price'));
        });
    } else {
        sortedProducts = originalProducts;
    }

    sortedProducts.forEach(function(product) {
        productContainer.appendChild(product);
    });
});



function cart() {
  const cart = document.getElementById('cart');
  if (!cart) return;


  const isVisible = cart.style.display !== 'none';
  if (isVisible) {
    cart.style.display = 'none';
    return;
  }
  cart.style.display = 'block';



}



let isShown = false;
function extraFilter() {
  const hiddenFilters = document.querySelectorAll('#extrafilter');
  const btn = document.getElementById('extraFilterBtn');

  if (isShown) {

    hiddenFilters.forEach(filter => {
      filter.style.display = "none";
    });
    isShown = false;
    btn.innerHTML = "See more";
  } else {
    hiddenFilters.forEach(filter => {
      filter.style.display = "block";
    });
    isShown = true; 
    btn.innerHTML = "See less";
  }
}



