window.uploadPhoto = ({ url, onUpload }) => {
    return {
        url: url,
        onUpload: onUpload,
        upload(e) {
            if (!e.target.files.length) return;
            const data = new FormData();
            data.append('logo', e.target.files[0]);
            fetch(this.url, { method: 'POST', body: data }).then(() => this.onUpload());
        },
        select() {
            document.getElementById('photo').click();
        },
    };
};
