(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        const inputPhoto   = document.getElementById('input-profile-photo');
        const imgPreview   = document.getElementById('profile-img-preview');
        const avatarCircle = document.getElementById('profile-avatar-circle');
        const fallbackEl   = document.getElementById('profile-avatar-fallback');
        const btnRemove    = document.getElementById('btn-remove-profile-photo');
        const inputHapus   = document.getElementById('input-hapus-foto');

        if (inputPhoto) {
            if (avatarCircle) {
                avatarCircle.addEventListener('click', function () {
                    inputPhoto.click();
                });
            }

            inputPhoto.addEventListener('change', function () {
                const file = this.files && this.files[0] ? this.files[0] : null;
                if (!file) return;

                if (!file.type || file.type.indexOf('image/') !== 0) {
                    alert('Harap pilih file gambar (JPG, PNG, WEBP).');
                    this.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file foto maksimal 5MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (imgPreview) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                    }
                    if (fallbackEl) {
                        fallbackEl.style.display = 'none';
                    }
                    if (inputHapus) {
                        inputHapus.value = '0';
                    }
                    if (btnRemove) {
                        btnRemove.style.display = 'inline-flex';
                    }
                };
                reader.readAsDataURL(file);
            });
        }

        if (btnRemove) {
            btnRemove.addEventListener('click', function (e) {
                e.preventDefault();
                if (inputPhoto) {
                    inputPhoto.value = '';
                }
                if (imgPreview) {
                    imgPreview.src = '';
                    imgPreview.style.display = 'none';
                }
                if (fallbackEl) {
                    fallbackEl.style.display = 'flex';
                }
                if (inputHapus) {
                    inputHapus.value = '1';
                }
                btnRemove.style.display = 'none';
            });
        }

        const tagsContainer = document.getElementById('profile-tags-container');
        const hiddenInput   = document.getElementById('input-bidang-minat') || document.getElementById('input-bidang-keahlian');
        const btnShowAddTag = document.getElementById('btn-show-add-tag');
        const addTagBox     = document.getElementById('add-tag-box');
        const selectNewTag  = document.getElementById('select-new-tag');
        const btnConfirmAdd = document.getElementById('btn-confirm-add-tag');
        const btnCancelAdd  = document.getElementById('btn-cancel-add-tag');

        if (!tagsContainer || !hiddenInput) return;

        function syncHiddenInput() {
            const pills  = tagsContainer.querySelectorAll('.profile-tag-pill');
            const values = [];
            pills.forEach(function (pill) {
                const val = pill.getAttribute('data-tag');
                if (val && values.indexOf(val) === -1) {
                    values.push(val);
                }
            });
            hiddenInput.value = values.join(', ');
        }

        tagsContainer.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('tag-remove-btn')) {
                const pill = e.target.closest('.profile-tag-pill');
                if (pill) {
                    pill.style.opacity = '0';
                    pill.style.transform = 'scale(0.8)';
                    setTimeout(function () {
                        pill.remove();
                        syncHiddenInput();
                    }, 140);
                }
            }
        });

        if (btnShowAddTag && addTagBox) {
            btnShowAddTag.addEventListener('click', function () {
                addTagBox.classList.add('show');
                if (selectNewTag) selectNewTag.focus();
            });
        }

        if (btnCancelAdd && addTagBox) {
            btnCancelAdd.addEventListener('click', function () {
                addTagBox.classList.remove('show');
                if (selectNewTag) selectNewTag.value = '';
            });
        }

        if (btnConfirmAdd && selectNewTag) {
            btnConfirmAdd.addEventListener('click', function () {
                const tagValue = selectNewTag.value ? selectNewTag.value.trim() : '';
                if (!tagValue) {
                    alert('Silakan pilih salah satu bidang dari daftar.');
                    return;
                }

                const existingPills = tagsContainer.querySelectorAll('.profile-tag-pill');
                let isDuplicate = false;
                existingPills.forEach(function (p) {
                    const existing = p.getAttribute('data-tag') || '';
                    if (existing.toLowerCase() === tagValue.toLowerCase()) {
                        isDuplicate = true;
                    }
                });

                if (isDuplicate) {
                    alert('Bidang "' + tagValue + '" sudah ditambahkan.');
                    return;
                }

                const newPill = document.createElement('span');
                newPill.className = 'profile-tag-pill';
                newPill.setAttribute('data-tag', tagValue);

                const labelSpan = document.createElement('span');
                labelSpan.className = 'tag-label';
                labelSpan.textContent = tagValue;

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'tag-remove-btn';
                removeBtn.title = 'Hapus ' + tagValue;
                removeBtn.setAttribute('aria-label', 'Hapus ' + tagValue);
                removeBtn.textContent = '✕';

                newPill.appendChild(labelSpan);
                newPill.appendChild(removeBtn);

                tagsContainer.insertBefore(newPill, btnShowAddTag);
                syncHiddenInput();

                selectNewTag.value = '';
                if (addTagBox) addTagBox.classList.remove('show');
            });
        }
    });
})();
