const hamburger = document.getElementById('hamburger');
const navMobile = document.getElementById('nav-mobile');

hamburger.addEventListener('click', () => {
  navMobile.classList.toggle('open');
});

function fecharMenu() {
  navMobile.classList.remove('open');
}

document.getElementById('telefone').addEventListener('input', function (e) {
  let v = e.target.value.replace(/\D/g, '');
  if (v.length > 11) v = v.slice(0, 11);
  if (v.length > 6) {
    v = '(' + v.slice(0,2) + ') ' + v.slice(2,7) + '-' + v.slice(7);
  } else if (v.length > 2) {
    v = '(' + v.slice(0,2) + ') ' + v.slice(2);
  } else if (v.length > 0) {
    v = '(' + v;
  }
  e.target.value = v;
});

async function enviarFormulario() {
  const nome     = document.getElementById('nome').value.trim();
  const telefone = document.getElementById('telefone').value.trim();
  const email    = document.getElementById('email').value.trim();
  const pacote   = document.getElementById('pacote').value;
  const pessoas  = document.getElementById('pessoas').value;
  const data     = document.getElementById('data').value;
  const mensagem = document.getElementById('mensagem').value.trim();

  if (!nome || !telefone || !pacote || !pessoas) {
    alert('Por favor, preencha todos os campos obrigatórios (*).');
    return;
  }

  const btn = document.querySelector('.btn-submit');
  btn.disabled = true;
  btn.textContent = 'Enviando...';

  try {
    const response = await fetch('salvar.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nome, telefone, email, pacote, pessoas, data, mensagem })
    });

    if (response.ok) {
      document.getElementById('reserva-form').style.display = 'none';
      document.getElementById('mensagem-sucesso').style.display = 'block';
      document.getElementById('mensagem-sucesso').scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      throw new Error('Erro no servidor');
    }
  } catch (err) {
    document.getElementById('mensagem-erro').style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Quero ser contactado →';
  }
}

const progressBar = document.getElementById('progressBar');

window.addEventListener('scroll', () => {
  const header = document.querySelector('header');
  if (window.scrollY > 60) {
    header.style.boxShadow = '0 4px 24px rgba(0,0,0,0.35)';
  } else {
    header.style.boxShadow = '0 2px 12px rgba(0,0,0,0.25)';
  }

  const scrollTop = window.scrollY;
  const docHeight = document.documentElement.scrollHeight - window.innerHeight;
  const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
  progressBar.style.width = progress + '%';
});

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal-up').forEach(el => observer.observe(el));
