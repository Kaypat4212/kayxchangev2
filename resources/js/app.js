import './bootstrap';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
import validateBtcAddress from 'bitcoin-address-validation';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


// Optionally import ethers and tronweb if needed
window.validateBtcAddress = validateBtcAddress; // Make available globally

require('./echo');