import {
    Component,
    OnInit,
    AfterViewInit,
    ViewEncapsulation,
    Renderer2,
    Input,
    Output,
    EventEmitter,
    ViewChild,
    ElementRef,
    OnDestroy,
} from "@angular/core";
import Keyboard, { KeyboardInput, KeyboardLayoutObject } from "simple-keyboard";
import { VirtualKeyboardService } from "app/_services/virtual-keyboard.service";
import { KeyboardConfigService, KeyboardConfig } from "app/_services/keyboard-config.service";

/**
 * VirtualKeyboard is a wrapper for the third-party
 * Simple-Keyboard (https://hodgef.com/simple-keyboard/).
 * Most things are self-contained, but the inputs that the
 * keyboard is operating on are not. They should be in the
 * parent component.
 * Some events that need to be passed from parent to child:
 *   - Input focus
 *   - Input value change
 *   - Input fields two-way data binding
 * Some events that need to be passed from child to parent:
 *   - Enter key is pressed
 */
@Component({
    selector: "app-virtual-keyboard",
    encapsulation: ViewEncapsulation.None,
    templateUrl: "./virtual-keyboard.component.html",
    styleUrls: [
        "./virtual-keyboard.component.scss",
        "../../../../node_modules/simple-keyboard/build/css/index.css",
    ],
})
/**
 * Inputs:
 *   - inputs, object
 *   - focusedInputId, string
 * Use:
 *   - <app-keyboard
 *         [inputs]="<parentInputs>"
 *         [focusedInputId]="<parentName>"
 *         [debug]="<parentDebugVar>"
 *     </app-keyboard>
 */
export class VirtualKeyboardComponent implements OnInit, OnDestroy, AfterViewInit {
    // Parent component defines if app is debug mode
    @Input() debug: boolean = false;
    // Parent component sends inputs to use virtual keyboard on
    @Input() inputs: KeyboardInput;
    // Class for keyboard
    @Input() customClass: string;
    // This component sends SimpleKeyboard virtual key strings to parent when pressed
    @Output() newVirtualKeyPress = new EventEmitter<string>();
    // This component send SimpleKeyboard physical key strings to parent when pressed
    @Output() newPhysicalKeyPress = new EventEmitter<string>();
    // This component sends SimpleKeyboard latest input value to parent when changed
    @Output() inputValueChanged = new EventEmitter<{ inputName: string; value: string }>();
    // This component sends SimpleKeyboard virtual key strings to parent when pressed
    @Output() keyboardReady = new EventEmitter<void>();
    // This component's outer div element
    @ViewChild("keyboard") mainDiv: ElementRef;
    // Close button, so we don't add so much padding that we see white space
    @ViewChild("closeButton") closeButton: ElementRef;

    private langSwitchKeyLeft = "{metaleft}";
    private langSwitchKeyRight = "{metaright}";
    public focusedInputId: string = "";
    public showKeyboard: boolean = false;
    public keyboard: Keyboard;
    private keyboardConfig: KeyboardConfig | null = null;
    private configLoaded = false;
    private domReady = false;

    constructor(
        private renderer2: Renderer2,
        private virtualKeyboardService: VirtualKeyboardService,
        private keyboardConfigService: KeyboardConfigService,
    ) {}

    private unlisteners: any[] = [];

    ngOnInit(): void {
        // Load keyboard configuration
        this.keyboardConfigService.loadConfig().subscribe((config) => {
            this.keyboardConfig = config;
            this.configLoaded = true;

            // Try to initialize keyboard if DOM is ready
            this.tryInitializeKeyboard();
        });

        // Listen to keydown events from the physical keyboard
        this.unlisteners.push(
            this.renderer2.listen("document", "keydown", (event) => this.onPhysicalKeyDown(event)),
        );
        // Listen to keyup events from the physical keyboard
        this.unlisteners.push(
            this.renderer2.listen("document", "keyup", (event) => this.onPhysicalKeyUp(event)),
        );
        this.unlisteners.push(
            this.renderer2.listen("document", "click", (event) => this.onClickOrTap(event)),
        );
    }

    ngOnDestroy() {
        delete this.keyboard;
        this.unlisteners.forEach((callback) => callback());
    }

    ngAfterViewInit() {
        this.domReady = true;
        // Try to initialize keyboard if config is loaded
        this.tryInitializeKeyboard();
    }

    private tryInitializeKeyboard(): void {
        // Only create keyboard when both config is loaded and DOM is ready
        if (!this.configLoaded || !this.domReady) {
            return;
        }

        // Create SimpleKeyboard with necessary options.
        try {
            const divClass = `.${this.customClass}`;
            this.keyboard = new Keyboard(divClass, {
                debug: this.debug,
                input: this.inputs,
                layout: this.keyboardConfig.defaultLayoutObject,
                inputName: this.focusedInputId ?? "",
                onChange: (value) => this.onInputValueChange(value),
                onKeyPress: (key) => this.onVirtualKeyPress(key),
                onKeyReleased: (key) => this.onVirtualKeyRelease(key),
                preventMouseDownDefault: true, // If you want to keep focus on input
                physicalKeyboardHighlight: true,
                physicalKeyboardHightlightPress: true,
                tabCharOnTab: false,
                mergeDisplay: true,
                theme: "hg-theme-default hg-layout-default elearning-theme",
                display: {
                    "{bksp}": "\u232b",
                    "{backspace}": "\u232b",
                    "{enter}": "\u23ce",
                    "{capslock}": "\u21ea",
                    "{lock}": "\u21ea",
                    "{shift}": "\u21e7",
                    "{shiftleft}": "\u21e7",
                    "{shiftright}": "\u21e7",
                    "{tab}": "\u21e5",
                    "{metaleft}":
                        this.keyboardConfig?.settings?.languageSwitchKeys?.left || "\u{1F310}",
                    "{metaright}":
                        this.keyboardConfig?.settings?.languageSwitchKeys?.right || "\u{1F310}",
                },
                buttonTheme: this.getButtonThemes(),
            });
            this.keyboard.replaceInput(this.inputs);
            this.keyboardReady.emit();
        } catch (err) {
            console.error("Error creating keyboard: ", err);
        }
    }

    private getCurrentLayoutObject(): KeyboardLayoutObject | null {
        if (!this.keyboardConfig) {
            return null;
        }
        return this.keyboardConfig.defaultLayoutObject;
    }

    private getButtonThemes(): any[] {
        if (!this.keyboardConfig?.buttonThemes) return [];
        return this.keyboardConfig.buttonThemes.map((theme) => ({
            class: theme.class,
            buttons: theme.buttons,
        }));
    }

    /**
     * Whether SimpleKeyboard key string corresponds to a shift key.
     * @param {string} key - String code from SimpleKeyboard
     * @returns {boolean} - True if shift key, false otherwise
     */
    virtualKeyIsShift(key: string): boolean {
        return ["{shift}", "{shiftleft}", "{shiftright}"].indexOf(key) >= 0;
    }

    physicalKeyIsShift(event: KeyboardEvent): boolean {
        return event.code === "Shift" || event.key === "Shift";
    }

    /**
     * Whether SimpleKeyboard key string corresponds to a caps lock key.
     * @param {string} key - String code from SimpleKeyboard
     * @returns {boolean} - True if caps lock key, false otherwise
     */
    virtualKeyIsCapsLock(key: string): boolean {
        return ["{capslock}", "{lock}"].indexOf(key) >= 0;
    }

    physicalKeyIsCapsLock(event: KeyboardEvent) {
        return event.code === "CapsLock" || event.key === "CapsLock";
    }

    /**
     * Converts the physical key pressed on the user's keyboard to whatever
     * character that key on the virtual keyboard would type with the current
     * layout.
     * @param {string} code - KeyboardEvent.code shift value that corresponds to
     * the physical keyboard key (which should be layout agnostic),
     * not the character that gets typed.
     * @returns Character from virtual keyboard layout
     */
    physicalKeyToLayoutChar(code: string): string {
        if (!this.keyboardConfig || !this.keyboard) return "";

        let layoutRow: number = -1;
        let layoutColumn: number = -1;
        let char: string = "";

        const keyCodes = Object.keys(this.keyboardConfig.keyCodesConversions);
        for (let i = 0; i < keyCodes.length; i++) {
            const keyCodesRowSplit = keyCodes[i].split(" ");
            if (keyCodesRowSplit.indexOf(code) >= 0) {
                layoutRow = i;
                layoutColumn = keyCodesRowSplit.indexOf(code);
            }
        }

        if (layoutRow <= -1) {
            return char;
        }

        // Use the current layout from the keyboard config
        const layout = this.getCurrentLayoutObject();
        if (!layout) return char;

        char = layout.default[layoutRow].split(" ")[layoutColumn];
        return char;
    }

    /**
     * Append the given character to the element on which the event was triggered.
     * @param {string} char - Character to append to input or textarea
     * @param {KeyboardEvent} event - keydown event that we are modifying
     */
    appendCharacter(char: string, event: KeyboardEvent) {
        let target;
        const tagName = (<HTMLElement>event.target).tagName;

        // Typecast to actual type so we can modify 'value' property
        if (tagName === "TEXTAREA") {
            target = <HTMLTextAreaElement>event.target;
        } else if (tagName === "INPUT") {
            target = <HTMLInputElement>event.target;
        } else {
            console.warn(
                "Target to replace character in is neither input" +
                    " or textarea. It is a " +
                    tagName,
            );
            return;
        }
        // Add layout key to the text value in the HTML element
        target.value += char;
    }

    /**
     * For physical keys that should map to language-specific characters
     * make the replacement in the HTML element.
     * @param {KeyboardEvent} event - Keydown event from user
     */
    handleCharacterConversion(event: KeyboardEvent): void {
        let layoutChar: string = "";

        if (event.altKey || event.metaKey) {
            this.toggleLayoutObject();
            return;
        }

        // Don't process if current layout is the default layout
        const defaultLayout = this.getCurrentLayoutObject();
        if (!defaultLayout || this.keyboard.options.layoutName === "default") {
            return;
        }

        // Don't process if alt, ctrl, or super/cmd are pressed
        if (event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        // Get equivalent language character for that key
        layoutChar = this.physicalKeyToLayoutChar(event.code);

        // If different from default key, type language character
        if (layoutChar !== "" && layoutChar != event.key) {
            event.preventDefault();
            this.appendCharacter(layoutChar, event);
        }
    }

    /**
     * Callback for all clicks/taps.
     * Sets this.showKeyboard to true when an <input> or <textarea> element is clicked,
     * or the keyboard is clicked,
     * in order to show the keyboard when needed.
     * @param {object} event - Click event
     */
    onClickOrTap = (event: PointerEvent): void => {
        const el = <HTMLElement>event.target;
        const isTextElement = ["INPUT", "TEXTAREA"].indexOf(el.tagName) > -1;
        if (
            (isTextElement ||
                el === this.mainDiv.nativeElement ||
                this.mainDiv.nativeElement.contains(el)) &&
            !el.classList.contains("no-virtual-keyboard")
        ) {
            // <input>, <textarea> or virtual keyboard clicked. Show keyboard.
            if (isTextElement) {
                this.show();
            }
            // if (el.dataset.skbtn === this.langSwitchKeyRight
            //     || el.dataset.skbtn === this.langSwitchKeyLeft) {
            //     const input = <HTMLInputElement>document.getElementById(this.focusedInputId);
            //     const newVal = input.value.replace(this.langSwitchKey, '');
            //     if (!!input) {
            //         input.value = newVal;
            //     }
            //     this.inputs[this.focusedInputId] = newVal;
            //     this.keyboard.replaceInput(this.inputs);
            // }
        }
    };

    public show() {
        this.showKeyboard = true;
        this.virtualKeyboardService.keyboardVisible();

        setTimeout(() => {
            if (!!this.mainDiv.nativeElement && !!this.closeButton.nativeElement) {
                const keyboardHeight = this.mainDiv.nativeElement.clientHeight;
                this.adjustPageForKeyboard(keyboardHeight);
                this.virtualKeyboardService.keyboardHeightChanged(keyboardHeight);
            } else {
                console.warn(
                    "Keyboard and close button not found. Can't adjust page for keyboard.",
                );
            }
        }, 100);
    }

    public hide() {
        this.showKeyboard = false;
        this.virtualKeyboardService.keyboardHidden();
        this.adjustPageForKeyboard(0);
        this.virtualKeyboardService.keyboardHeightChanged(0);
    }

    private adjustPageForKeyboard(keyboardHeight: number = 0) {
        let openModalFound: boolean = false;

        // Get all modals and adjust padding if they are open
        const modals = document.getElementsByClassName("modal");
        if (modals?.length) {
            // Loop through all modals and adjust padding if the modal is open
            Array.from(modals).forEach((modal) => {
                if (modal.classList.contains("show")) {
                    (modal as HTMLElement).style.paddingBottom = `${keyboardHeight}px`;
                    openModalFound = true;
                } else {
                    (modal as HTMLElement).style.paddingBottom = "";
                }
            });
        }

        // If no modals are open, adjust padding for the body
        if (!openModalFound) {
            document.body.style.paddingBottom = `${keyboardHeight}px`;
        }
    }

    /**
     * Callback for all key presses from a physical keyboard.
     * Handles special keys.
     * Language toggle key is not handled on the physical keyboard because
     * events aren't trigged with just the Alt key, since it's a special key.
     * @param {KeyboardEvent} event - Physical key press event
     */
    onPhysicalKeyDown = (event: KeyboardEvent): void => {
        if (Object.keys(this.keyboard.input).indexOf((<HTMLElement>event.target).id) < 0) {
            // No input in focus
            return;
        }

        // Emit SimpleKeyboard key string to parent component
        this.newPhysicalKeyPress.emit(event.key);

        if (this.physicalKeyIsShift(event)) {
            this.handleShiftDown();
        } else {
            this.handleCharacterConversion(event);
        }
    };

    /**
     * Callback for all key releases from a physical keyboard.
     * Handles special keys.
     * @param {KeyboardEvent} event - Physical key release event
     */
    onPhysicalKeyUp = (event: KeyboardEvent): void => {
        if (this.physicalKeyIsShift(event)) {
            this.handleShiftUp();
        }
    };

    /**
     * Callback for all key presses from the virtual keyboard.
     * Handles special keys.
     * @param {string} key - String code of virtual key that was pressed
     */
    onVirtualKeyPress = (key: string): void => {
        /**
         * If you want to handle the shift and caps lock buttons
         */
        if (this.virtualKeyIsShift(key)) {
            this.handleShiftDown();
        } else if (this.virtualKeyIsCapsLock(key)) {
            this.handleShiftDown();
        } else if (key === this.langSwitchKeyLeft || key === this.langSwitchKeyRight) {
            this.toggleLayoutObject();
        }
        // Emit SimpleKeyboard key string to parent component
        this.newVirtualKeyPress.emit(key);
    };

    /**
     * Callback for all key releases from the virtual keyboard.
     * Handles special keys.
     * @param {string} key - String code of virtual key that was released
     */
    onVirtualKeyRelease = (key: string): void => {
        if (this.virtualKeyIsShift(key)) {
            this.handleShiftUp();
        } else if (this.virtualKeyIsCapsLock(key)) {
            this.handleShiftUp();
        }
    };

    /**
     * Handles the normal function of shift but for either the language-specific
     * layout or the English layout, whichever is active.
     */
    handleShiftUp(): void {
        this.keyboard.setOptions({
            layoutName: "default",
        });
    }

    handleShiftDown(): void {
        this.keyboard.setOptions({
            layoutName: "shift",
        });
    }

    /**
     * Toggles the layout group between default and alternate layouts
     */
    toggleLayoutObject(): void {
        if (!this.keyboardConfig) {
            console.warn("⚠️ No keyboard config found. Can't toggle layout.");
            return;
        }

        let newLayout: KeyboardLayoutObject;
        if (this.keyboard.options.layout === this.keyboardConfig.defaultLayoutObject) {
            newLayout = this.keyboardConfig.alternateLayoutObject;
        } else {
            newLayout = this.keyboardConfig.defaultLayoutObject;
        }
        this.keyboard.setOptions({
            layout: newLayout,
        });
    }

    /**
     * Simple Keyboard event callback
     * @param {Event} event - Input element focus event
     */
    onInputFocus = (event: Event): void => {
        this.focusedInputId = (<HTMLInputElement>event.target).id;
        if (!this.focusedInputId) {
            console.warn("No input name found for focused input.");
            return;
        }

        this.keyboard.setOptions({
            inputName: this.focusedInputId,
        });
    };

    /**
     * Simple Keyboard event callback
     */
    setInputCaretPosition = (elem: any, pos: number): void => {
        if (elem.setSelectionRange) {
            elem.focus();
            elem.setSelectionRange(pos, pos);
        }
    };

    /**
     * Simple Keyboard event callback
     * @param {Event} event - Event that triggered the change
     */
    onInputChange = (event: Event): void => {
        this.keyboard.setInput(
            (<HTMLInputElement>event.target).value,
            (<HTMLInputElement>event.target).id,
        );
    };

    /**
     * Callback for when the value of the input changes. This is helpful for components
     * that use one-way binding, like reactive forms, instead of the two-way binding of ngModel.
     * @param {string} value - New input value
     */
    onInputValueChange = (value: string): void => {
        // Emit latest input value to parent component as object with input name and value
        this.inputValueChanged.emit({
            inputName: this.focusedInputId,
            value,
        });
    };
}
