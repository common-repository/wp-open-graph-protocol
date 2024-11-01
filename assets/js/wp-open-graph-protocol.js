(function ($) {
  const $url = $('#wpogp-media-url')
  const $image = $('#wpogp-media-image')
  const $select = $('#wpogp-media-select')
  const $clear = $('#wpogp-media-clear')

  let uploader

  $select.on('click', function (e) {
    e.preventDefault()

    if(uploader) {
      uploader.open()
      return
    }

    uploader = wp.media({
      title: 'Select Image',

      library: {
        type: 'image'
      },

      button: {
        text: 'Select Image'
      },

      multiple: false
    })

    uploader.on('select', function () {
      const images = uploader.state().get('selection')

      // dataに選択した画像の情報が入ってる
      images.each(function (data) {
        const url = data.attributes.url
        $url.val(url)
        $image.attr('src', url)
      })
    })

    uploader.open()
  })

  $clear.on('click', function () {
    $url.val('')
    $image.attr('src', '')
  })
})(jQuery)
