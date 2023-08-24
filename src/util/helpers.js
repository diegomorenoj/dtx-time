export function leadingSlash (str) {
  return str.startsWith('/') ? str : '/' + str;
}

export function trailingSlash (str) {
  return str.endsWith('/') ? str : str + '/';
};

export const wait = timeout => {
  return new Promise(resolve => setTimeout(resolve, timeout));
};

export function getErrorMessage (error) {
  if (typeof error === 'string') return error;
  // CONVERTIR EL ERROR ARRAY EN STRING
  let strError = '';
  for (const property in error) {
    if (property) strError += `<li>${error[property]}</li>`;
  }
  return strError;
};
