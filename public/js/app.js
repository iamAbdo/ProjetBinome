document.addEventListener('DOMContentLoaded', () => {
  const addToCartButtons = document.querySelectorAll('[data-add-cart]');
  const cartCountEls = document.querySelectorAll('[data-cart-count]');
  const qtyInputs = document.querySelectorAll('[data-item-qty]');
  const removeButtons = document.querySelectorAll('[data-remove-item]');
  const emptyCartBtn = document.querySelector('[data-empty-cart]');
  const form = document.querySelector('#order-form');
  const statusBox = document.querySelector('#order-status');

  const CART_COOKIE = 'cart_items';
  const CART_MAX_AGE = 60 * 60 * 24 * 7; // 7 jours

  const readCart = () => {
    const cookieString = document.cookie
      .split(';')
      .map((part) => part.trim())
      .find((row) => row.startsWith(`${CART_COOKIE}=`));

    if (!cookieString) {
      return [];
    }

    try {
      const value = decodeURIComponent(cookieString.split('=')[1]);
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      console.error('Cannot parse cart cookie', error);
      return [];
    }
  };

  const storeCart = (items) => {
    if (!items.length) {
      document.cookie = `${CART_COOKIE}=;path=/;max-age=0;SameSite=Lax`;
      return;
    }

    document.cookie = `${CART_COOKIE}=${encodeURIComponent(JSON.stringify(items))};path=/;max-age=${CART_MAX_AGE};SameSite=Lax`;
  };

  const updateCartCount = (items = readCart()) => {
    const count = items.reduce((total, item) => total + (Number(item.qty) || 0), 0);
    cartCountEls.forEach((el) => {
      el.textContent = count.toString();
    });
  };

  const provideFeedback = (button) => {
    const original = button.textContent;
    button.textContent = 'Ajouté !';
    button.disabled = true;
    setTimeout(() => {
      button.textContent = original;
      button.disabled = false;
    }, 1200);
  };

  addToCartButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const id = Number(button.dataset.id);
      const name = button.dataset.name || '';
      const price = Number(button.dataset.price || 0);
      const image = button.dataset.image || '';

      if (!id || !name) {
        return;
      }

      const cart = readCart();
      const existing = cart.find((item) => item.id === id);

      if (existing) {
        existing.qty += 1;
      } else {
        cart.push({ id, name, price, image, qty: 1 });
      }

      storeCart(cart);
      updateCartCount(cart);
      provideFeedback(button);
    });
  });

  qtyInputs.forEach((input) => {
    input.addEventListener('change', () => {
      const id = Number(input.dataset.itemQty);
      let value = Number(input.value);

      if (!Number.isFinite(value) || value < 1) {
        value = 1;
        input.value = '1';
      }

      const cart = readCart();
      const item = cart.find((entry) => entry.id === id);
      if (!item) return;

      item.qty = value;
      storeCart(cart);
      updateCartCount(cart);
      window.location.reload();
    });
  });

  removeButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = Number(btn.dataset.removeItem);
      const cart = readCart().filter((item) => item.id !== id);
      storeCart(cart);
      updateCartCount(cart);
      window.location.reload();
    });
  });

  if (emptyCartBtn) {
    emptyCartBtn.addEventListener('click', () => {
      storeCart([]);
      updateCartCount([]);
      window.location.reload();
    });
  }

  if (form && statusBox) {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      statusBox.textContent = 'Envoi en cours...';
      statusBox.className = 'alert';

      try {
        const response = await fetch('order-handler.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(form),
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || 'Erreur serveur');
        }

        statusBox.textContent = data.message;
        statusBox.className = 'alert alert-success';
        form.reset();
        storeCart([]);
        updateCartCount([]);
        setTimeout(() => window.location.reload(), 1400);
      } catch (error) {
        statusBox.textContent = error.message;
        statusBox.className = 'alert alert-error';
      }
    });
  }

  updateCartCount();
});
