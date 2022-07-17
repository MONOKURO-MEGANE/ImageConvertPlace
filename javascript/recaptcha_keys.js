function getSitekey() {
  let site_key = "";
  $.ajax({
    async: false,
    url: '../_recaptcha_keys.txt',
  })
  .done(function(data, textStatus, jqXHR) {
    site_key = data;
  });
  return site_key;
}
