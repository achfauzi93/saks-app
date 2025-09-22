export function formatTanggalIndo(tanggal, withTime = false) {
    if (!tanggal) return '-';

    try {
        const date = new Date(tanggal);

        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            ...(withTime && { hour: '2-digit', minute: '2-digit' }),
        };

        return new Intl.DateTimeFormat('id-ID', options).format(date);
    } catch (e) {
        console.error('Format tanggal gagal:', e);
        return '-';
    }
}
