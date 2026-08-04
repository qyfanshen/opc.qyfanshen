(() => {
  const form = document.querySelector('#signupForm');
  const message = document.querySelector('#formMessage');
  const button = form?.querySelector('.submit-btn');

  document.querySelectorAll('.reveal').forEach((element) => {
    if (!('IntersectionObserver' in window)) return element.classList.add('visible');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08 });
    observer.observe(element);
  });

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    message.className = 'form-message';
    if (!form.reportValidity()) return;

    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span>正在提交…</span>';
    const payload = Object.fromEntries(new FormData(form).entries());
    payload.consent = Boolean(form.elements.consent.checked);

    try {
      const response = await fetch('/api/submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error(result.message || '提交失败');
      message.className = 'form-message success';
      message.textContent = `报名成功！你的报名编号是 ${result.code}，请妥善保存。`;
      form.reset();
      message.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } catch (error) {
      message.className = 'form-message error';
      message.textContent = error.message || '网络异常，请稍后重试。';
    } finally {
      button.disabled = false;
      button.innerHTML = original;
    }
  });
})();
