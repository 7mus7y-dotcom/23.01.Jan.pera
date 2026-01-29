(() => {
  const data = window.peraFavourites || {};
  const ajaxUrl = data.ajax_url || '';
  const nonce = data.nonce || '';
  const isLoggedIn = Boolean(data.is_logged_in);
  const storageKey = 'pera_favourites';

  const parseIds = (value) => {
    if (!Array.isArray(value)) {
      return [];
    }
    return value
      .map((id) => parseInt(id, 10))
      .filter((id) => Number.isFinite(id) && id > 0);
  };

  const readLocal = () => {
    try {
      const raw = window.localStorage.getItem(storageKey);
      if (!raw) {
        return [];
      }
      const parsed = JSON.parse(raw);
      return parseIds(parsed);
    } catch (err) {
      return [];
    }
  };

  const writeLocal = (ids) => {
    try {
      window.localStorage.setItem(storageKey, JSON.stringify(Array.from(ids)));
    } catch (err) {
      // ignore storage errors
    }
  };

  let favourites = new Set(readLocal());

  const updateButton = (button, isFav) => {
    button.classList.toggle('is-fav', isFav);
    button.setAttribute('aria-pressed', isFav ? 'true' : 'false');
    button.setAttribute(
      'aria-label',
      isFav ? 'Remove from favourites' : 'Add to favourites'
    );
  };

  const updateButtonsForId = (postId) => {
    const isFav = favourites.has(postId);
    document
      .querySelectorAll(`.fav-toggle[data-post-id="${postId}"]`)
      .forEach((button) => updateButton(button, isFav));
  };

  const updateAllButtons = () => {
    document.querySelectorAll('.fav-toggle').forEach((button) => {
      const postId = parseInt(button.dataset.postId, 10);
      if (!Number.isFinite(postId) || postId <= 0) {
        return;
      }
      updateButton(button, favourites.has(postId));
    });
  };

  const fetchServerFavourites = async () => {
    if (!isLoggedIn || !ajaxUrl) {
      return;
    }

    const body = new URLSearchParams();
    body.set('action', 'pera_get_favourites');
    body.set('nonce', nonce);

    try {
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body,
        credentials: 'same-origin',
      });

      const payload = await response.json();
      if (!payload || !payload.success) {
        return;
      }

      const serverFavs = parseIds(payload.data && payload.data.favourites);
      serverFavs.forEach((id) => favourites.add(id));
      writeLocal(favourites);
      updateAllButtons();
    } catch (err) {
      // ignore fetch errors
    }
  };

  const persistServerToggle = async (postId, nextIsFav) => {
    if (!isLoggedIn || !ajaxUrl) {
      return { ok: true };
    }

    const body = new URLSearchParams();
    body.set('action', 'pera_toggle_favourite');
    body.set('nonce', nonce);
    body.set('post_id', String(postId));
    body.set('fav_action', nextIsFav ? 'add' : 'remove');

    try {
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body,
        credentials: 'same-origin',
      });

      const payload = await response.json();
      if (!payload || !payload.success) {
        return { ok: false };
      }

      const serverFavs = parseIds(payload.data && payload.data.favourites);
      favourites = new Set(serverFavs);
      writeLocal(favourites);
      updateAllButtons();

      return { ok: true };
    } catch (err) {
      return { ok: false };
    }
  };

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.fav-toggle');
    if (!button) {
      return;
    }

    event.preventDefault();

    const postId = parseInt(button.dataset.postId, 10);
    if (!Number.isFinite(postId) || postId <= 0) {
      return;
    }

    const wasFav = favourites.has(postId);
    const nextIsFav = !wasFav;

    if (nextIsFav) {
      favourites.add(postId);
    } else {
      favourites.delete(postId);
    }

    writeLocal(favourites);
    updateButtonsForId(postId);

    persistServerToggle(postId, nextIsFav).then((result) => {
      if (result.ok) {
        return;
      }

      if (nextIsFav) {
        favourites.delete(postId);
      } else {
        favourites.add(postId);
      }

      writeLocal(favourites);
      updateButtonsForId(postId);
    });
  });

  updateAllButtons();
  fetchServerFavourites();
})();
