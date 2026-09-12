'use strict';
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
if (!reduced && 'IntersectionObserver' in window) {
 document.documentElement.classList.add('js-motion');
 const observer = new IntersectionObserver(entries => entries.forEach(entry => {
  if (entry.isIntersecting) { entry.target.classList.add('visible'); observer.unobserve(entry.target); }
 }), { threshold: 0.08 });
 document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
 document.querySelectorAll('.feature-card').forEach(card => card.addEventListener('pointermove', event => {
  const box = card.getBoundingClientRect();
  card.style.setProperty('--mx', `${event.clientX - box.left}px`);
  card.style.setProperty('--my', `${event.clientY - box.top}px`);
 }));
}
document.querySelector('.show-password')?.addEventListener('click', event => {
 const field = document.querySelector('#password');
 const show = field.type === 'password';
 field.type = show ? 'text' : 'password';
 event.currentTarget.textContent = show ? 'Hide' : 'Show';
 event.currentTarget.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
});
document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
 const element = document.getElementById(button.dataset.copy);
 try {
  await navigator.clipboard.writeText(element.textContent);
  button.textContent = 'Copied ✓';
 } catch {
  const selection = window.getSelection(); const range = document.createRange();
  range.selectNodeContents(element); selection.removeAllRanges(); selection.addRange(range);
  button.textContent = 'Selected — press Ctrl+C';
 }
}));
document.querySelectorAll('form').forEach(form => form.addEventListener('submit', event => {
 if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) { event.preventDefault(); return; }
 const submit = form.querySelector('button[type="submit"]');
 if (submit) { submit.disabled = true; submit.textContent = 'Please wait…'; }
}));
