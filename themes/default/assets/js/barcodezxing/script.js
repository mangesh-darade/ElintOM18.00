window.addEventListener('load', function () {
    let selectedDeviceId;
    var hints = new Map();
    hints.set(ZXing.DecodeHintType.ASSUME_GS1, true);
    hints.set(ZXing.DecodeHintType.TRY_HARDER, true);
    const codeReader = new ZXing.BrowserMultiFormatReader(hints);
//    console.log('ZXing code reader initialized')
    codeReader.getVideoInputDevices()
      .then((videoInputDevices) => {
      const sourceSelect = document.getElementById('sourceSelect');
      const startButton = document.getElementById('startButton');
      const resetButton = document.getElementById('resetButton');
      if (videoInputDevices.length >= 1 && videoInputDevices[0]) {
      selectedDeviceId = videoInputDevices[0].deviceId;
        videoInputDevices.forEach((element) => {
          if (!sourceSelect) {
            return;
          }
          const sourceOption = document.createElement('option');
          sourceOption.text = element.label;
          sourceOption.value = element.deviceId;
          sourceSelect.appendChild(sourceOption);
        });

        if (sourceSelect) {
        sourceSelect.onchange = () => {
          selectedDeviceId = sourceSelect.value;
        };
        }

        const sourceSelectPanel = document.getElementById('sourceSelectPanel');
        if (sourceSelectPanel) {
        sourceSelectPanel.style.display = 'block';
        }
      }

      if (startButton) {
      startButton.addEventListener('click', () => {
        codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
          if (result) {
            console.log(result.getText())
            document.getElementById('result').textContent = result.text
          }
          if (err && !(err instanceof ZXing.NotFoundException)) {
            console.error(err)
            document.getElementById('result').textContent = err
          }
        })
        console.log(`Started continous decode from camera with id ${selectedDeviceId}`)
      })
      }

      if (resetButton) {
      resetButton.addEventListener('click', () => {
        codeReader.reset()
        document.getElementById('result').textContent = '';
        console.log('Reset.')
      })
      }

    })
      .catch((err) => {
      console.log(err);
    })
  }) 