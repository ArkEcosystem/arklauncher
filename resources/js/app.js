import Alpine from 'alpinejs';
import '@ui/js/tippy.js';
import Dropdown from '@ui/js/dropdown';
import Modal from '@ui/js/modal';
import Navbar from '@ui/js/navbar';
import Slider from '@ui/js/slider';
import Pagination from '@ui/js/pagination';
import RichSelect from '@ui/js/rich-select.js';
import './vendor/ark/reposition-dropdown';
import FileUpload from './file-upload.js';
import CookieBanner from '@ui/js/cookie-banner';
import '@ui/js/tabs.js';
import 'focus-visible';

window.Alpine = Alpine;
window.Dropdown = Dropdown;
window.Modal = Modal;
window.Navbar = Navbar;
window.Slider = Slider;
window.Pagination = Pagination;
window.RichSelect = RichSelect;
window.Slider = Slider;
window.FileUpload = FileUpload;
window.CookieBanner = CookieBanner;

Alpine.start();
