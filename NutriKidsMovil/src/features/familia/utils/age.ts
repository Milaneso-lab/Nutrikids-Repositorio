export function calculateAge(fechaNacimiento: string): number | null {
  const birth = new Date(fechaNacimiento);
  if (Number.isNaN(birth.getTime())) {
    return null;
  }

  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age -= 1;
  }

  return age >= 0 ? age : null;
}

export function formatAgeLabel(fechaNacimiento: string): string {
  const age = calculateAge(fechaNacimiento);
  if (age === null) {
    return 'Edad no disponible';
  }
  return age === 1 ? '1 año' : `${age} años`;
}
