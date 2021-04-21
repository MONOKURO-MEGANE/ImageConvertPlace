function getSitekey() {
  SITEKEY = "";
  $.ajax({
    async: false,
    url: '../.recaptcha_keys.txt',
  })
  .done(function(data, textStatus, jqXHR) {
    SITEKEY = data;
  });
  return SITEKEY;
}
