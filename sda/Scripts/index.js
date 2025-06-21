      function cadastroMV()
      {
      
      window.location = '../Layout/cadastro.html'

      }
      //cadastro animações

      const aceitar = document.querySelector("#btnAceitar")
      const avancar = document.querySelector('#btnAvancar')
      const checkboxterms = document.querySelector('#terms')
      const checkboxtag = document.querySelectorAll('#tag')
      const circlesteps = document.querySelectorAll('.circle')

      const circleOption = document.createElement('div')
      
      circleOption.classList.add('circle-option')
      circlesteps[0].appendChild(circleOption);


      

      const btns = [aceitar, avancar]
      let currentStep = 0

      aceitar.disabled = true
      avancar.disabled = true

      checkboxterms.addEventListener("change", ()=>{
            aceitar.disabled = !checkboxterms.checked
      })
      function verificarCheckboxes() {
            const marcadas = Array.from(checkboxtag).filter(cb => cb.checked).length;
            avancar.disabled = marcadas < 3
          }
          checkboxtag.forEach(cb => {
            cb.addEventListener('change', verificarCheckboxes);
          });


      const steps = document.querySelectorAll("#steps");

     function atualizarStep(){
      steps.forEach((step, index) =>{
            step.classList.toggle('active', index === currentStep);
            circlesteps[currentStep].appendChild(circleOption);

      });
     }
     btns.forEach((btn) =>{
      btn.addEventListener('click', (event)=>{
            event.preventDefault();

            if(currentStep < steps.length -1 ){
                  currentStep++;
                  atualizarStep();
            }
      })
     })