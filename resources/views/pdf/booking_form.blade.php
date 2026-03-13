<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PDF to HTML</title>
    <style>
        body {
            margin: 0;
        }

        /* Copyright 2014 Mozilla Foundation
   *
   * Licensed under the Apache License, Version 2.0 (the "License");
   * you may not use this file except in compliance with the License.
   * You may obtain a copy of the License at
   *
   *     http://www.apache.org/licenses/LICENSE-2.0
   *
   * Unless required by applicable law or agreed to in writing, software
   * distributed under the License is distributed on an "AS IS" BASIS,
   * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   * See the License for the specific language governing permissions and
   * limitations under the License.
   */

        .textLayer {
            position: absolute;
            text-align: initial;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            opacity: 1;
            line-height: 1;
            -webkit-text-size-adjust: none;
            -moz-text-size-adjust: none;
            text-size-adjust: none;
            forced-color-adjust: none;
            transform-origin: 0 0;
            z-index: 2;
        }

        .textLayer span,
        .textLayer br {
            color: black;
            position: absolute;
            white-space: pre;
            cursor: text;
            transform-origin: 0% 0%;
        }

        /* Only necessary in Google Chrome, see issue 14205, and most unfortunately
   * the problem doesn't show up in "text" reference tests. */
        .textLayer span.markedContent {
            top: 0;
            height: 0;
        }

        .textLayer .highlight {
            margin: -1px;
            padding: 1px;
            background-color: rgba(180, 0, 170, 1);
            border-radius: 4px;
        }

        .textLayer .highlight.appended {
            position: initial;
        }

        .textLayer .highlight.begin {
            border-radius: 4px 0 0 4px;
        }

        .textLayer .highlight.end {
            border-radius: 0 4px 4px 0;
        }

        .textLayer .highlight.middle {
            border-radius: 0;
        }

        .textLayer .highlight.selected {
            background-color: rgba(0, 100, 0, 1);
        }

        .textLayer ::-moz-selection {
            background: #0000ff26;
        }

        .textLayer ::selection {
            background: #0000ff26;
        }

        /* Avoids https://github.com/mozilla/pdf.js/issues/13840 in Chrome */
        .textLayer br::-moz-selection {
            background: transparent;
        }

        .textLayer br::selection {
            background: transparent;
        }

        .textLayer .endOfContent {
            display: block;
            position: absolute;
            left: 0;
            top: 100%;
            right: 0;
            bottom: 0;
            z-index: -1;
            cursor: default;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        .textLayer .endOfContent.active {
            top: 0;
        }


        :root {
            --annotation-unfocused-field-background: url("data:image/svg+xml;charset=UTF-8,<svg width='1px' height='1px' xmlns='http://www.w3.org/2000/svg'><rect width='100%' height='100%' style='fill:rgba(0, 54, 255, 0.13);'/></svg>");
            --input-focus-border-color: Highlight;
            --input-focus-outline: 1px solid Canvas;
            --input-unfocused-border-color: transparent;
            --input-disabled-border-color: transparent;
            --input-hover-border-color: black;
        }

        @media (forced-colors: active) {
            :root {
                --input-focus-border-color: CanvasText;
                --input-unfocused-border-color: ActiveText;
                --input-disabled-border-color: GrayText;
                --input-hover-border-color: Highlight;
            }

            .annotationLayer .textWidgetAnnotation input:required,
            .annotationLayer .textWidgetAnnotation textarea:required,
            .annotationLayer .choiceWidgetAnnotation select:required,
            .annotationLayer .buttonWidgetAnnotation.checkBox input:required,
            .annotationLayer .buttonWidgetAnnotation.radioButton input:required {
                outline: 1.5px solid selectedItem;
            }
        }

        .annotationLayer {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            transform-origin: 0 0;
            z-index: 3;
        }

        .annotationLayer section {
            position: absolute;
            text-align: initial;
            pointer-events: auto;
            box-sizing: border-box;
            transform-origin: 0 0;
        }

        .annotationLayer .linkAnnotation>a,
        .annotationLayer .buttonWidgetAnnotation.pushButton>a {
            position: absolute;
            font-size: 1em;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .annotationLayer .buttonWidgetAnnotation.pushButton>canvas {
            width: 100%;
            height: 100%;
        }

        .annotationLayer .linkAnnotation>a:hover,
        .annotationLayer .buttonWidgetAnnotation.pushButton>a:hover {
            opacity: 0.2;
            background: rgba(255, 255, 0, 1);
            box-shadow: 0 2px 10px rgba(255, 255, 0, 1);
        }

        .annotationLayer .textAnnotation img {
            position: absolute;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .annotationLayer .textWidgetAnnotation input,
        .annotationLayer .textWidgetAnnotation textarea,
        .annotationLayer .choiceWidgetAnnotation select,
        .annotationLayer .buttonWidgetAnnotation.checkBox input,
        .annotationLayer .buttonWidgetAnnotation.radioButton input {
            background-image: var(--annotation-unfocused-field-background);
            border: 2px solid var(--input-unfocused-border-color);
            box-sizing: border-box;
            font: calc(9px * var(--scale-factor)) sans-serif;
            height: 100%;
            margin: 0;
            vertical-align: top;
            width: 100%;
        }

        .annotationLayer .textWidgetAnnotation input:required,
        .annotationLayer .textWidgetAnnotation textarea:required,
        .annotationLayer .choiceWidgetAnnotation select:required,
        .annotationLayer .buttonWidgetAnnotation.checkBox input:required,
        .annotationLayer .buttonWidgetAnnotation.radioButton input:required {
            outline: 1.5px solid red;
        }

        .annotationLayer .choiceWidgetAnnotation select option {
            padding: 0;
        }

        .annotationLayer .buttonWidgetAnnotation.radioButton input {
            border-radius: 50%;
        }

        .annotationLayer .textWidgetAnnotation textarea {
            resize: none;
        }

        .annotationLayer .textWidgetAnnotation input[disabled],
        .annotationLayer .textWidgetAnnotation textarea[disabled],
        .annotationLayer .choiceWidgetAnnotation select[disabled],
        .annotationLayer .buttonWidgetAnnotation.checkBox input[disabled],
        .annotationLayer .buttonWidgetAnnotation.radioButton input[disabled] {
            background: none;
            border: 2px solid var(--input-disabled-border-color);
            cursor: not-allowed;
        }

        .annotationLayer .textWidgetAnnotation input:hover,
        .annotationLayer .textWidgetAnnotation textarea:hover,
        .annotationLayer .choiceWidgetAnnotation select:hover,
        .annotationLayer .buttonWidgetAnnotation.checkBox input:hover,
        .annotationLayer .buttonWidgetAnnotation.radioButton input:hover {
            border: 2px solid var(--input-hover-border-color);
        }

        .annotationLayer .textWidgetAnnotation input:hover,
        .annotationLayer .textWidgetAnnotation textarea:hover,
        .annotationLayer .choiceWidgetAnnotation select:hover,
        .annotationLayer .buttonWidgetAnnotation.checkBox input:hover {
            border-radius: 2px;
        }

        .annotationLayer .textWidgetAnnotation input:focus,
        .annotationLayer .textWidgetAnnotation textarea:focus,
        .annotationLayer .choiceWidgetAnnotation select:focus {
            background: none;
            border: 2px solid var(--input-focus-border-color);
            border-radius: 2px;
            outline: var(--input-focus-outline);
        }

        .annotationLayer .buttonWidgetAnnotation.checkBox :focus,
        .annotationLayer .buttonWidgetAnnotation.radioButton :focus {
            background-image: none;
            background-color: transparent;
        }

        .annotationLayer .buttonWidgetAnnotation.checkBox :focus {
            border: 2px solid var(--input-focus-border-color);
            border-radius: 2px;
            outline: var(--input-focus-outline);
        }

        .annotationLayer .buttonWidgetAnnotation.radioButton :focus {
            border: 2px solid var(--input-focus-border-color);
            outline: var(--input-focus-outline);
        }

        .annotationLayer .buttonWidgetAnnotation.checkBox input:checked:before,
        .annotationLayer .buttonWidgetAnnotation.checkBox input:checked:after,
        .annotationLayer .buttonWidgetAnnotation.radioButton input:checked:before {
            background-color: CanvasText;
            content: "";
            display: block;
            position: absolute;
        }

        .annotationLayer .buttonWidgetAnnotation.checkBox input:checked:before,
        .annotationLayer .buttonWidgetAnnotation.checkBox input:checked:after {
            height: 80%;
            left: 45%;
            width: 1px;
        }

        .annotationLayer .buttonWidgetAnnotation.checkBox input:checked:before {
            transform: rotate(45deg);
        }

        .annotationLayer .buttonWidgetAnnotation.checkBox input:checked:after {
            transform: rotate(-45deg);
        }

        .annotationLayer .buttonWidgetAnnotation.radioButton input:checked:before {
            border-radius: 50%;
            height: 50%;
            left: 30%;
            top: 20%;
            width: 50%;
        }

        .annotationLayer .textWidgetAnnotation input.comb {
            font-family: monospace;
            padding-left: 2px;
            padding-right: 0;
        }

        .annotationLayer .textWidgetAnnotation input.comb:focus {
            /*
     * Letter spacing is placed on the right side of each character. Hence, the
     * letter spacing of the last character may be placed outside the visible
     * area, causing horizontal scrolling. We avoid this by extending the width
     * when the element has focus and revert this when it loses focus.
     */
            width: 103%;
        }

        .annotationLayer .buttonWidgetAnnotation.checkBox input,
        .annotationLayer .buttonWidgetAnnotation.radioButton input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .annotationLayer .popupTriggerArea {
            height: 100%;
            width: 100%;
        }

        .annotationLayer .popupWrapper {
            position: absolute;
            font-size: calc(9px * var(--scale-factor));
            width: 100%;
            min-width: calc(180px * var(--scale-factor));
            pointer-events: none;
        }

        .annotationLayer .popup {
            position: absolute;
            max-width: calc(180px * var(--scale-factor));
            background-color: rgba(255, 255, 153, 1);
            box-shadow: 0 calc(2px * var(--scale-factor)) calc(5px * var(--scale-factor)) rgba(136, 136, 136, 1);
            border-radius: calc(2px * var(--scale-factor));
            padding: calc(6px * var(--scale-factor));
            margin-left: calc(5px * var(--scale-factor));
            cursor: pointer;
            font: message-box;
            white-space: normal;
            word-wrap: break-word;
            pointer-events: auto;
        }

        .annotationLayer .popup>* {
            font-size: calc(9px * var(--scale-factor));
        }

        .annotationLayer .popup h1 {
            display: inline-block;
        }

        .annotationLayer .popupDate {
            display: inline-block;
            margin-left: calc(5px * var(--scale-factor));
        }

        .annotationLayer .popupContent {
            border-top: 1px solid rgba(51, 51, 51, 1);
            margin-top: calc(2px * var(--scale-factor));
            padding-top: calc(2px * var(--scale-factor));
        }

        .annotationLayer .richText>* {
            white-space: pre-wrap;
            font-size: calc(9px * var(--scale-factor));
        }

        .annotationLayer .highlightAnnotation,
        .annotationLayer .underlineAnnotation,
        .annotationLayer .squigglyAnnotation,
        .annotationLayer .strikeoutAnnotation,
        .annotationLayer .freeTextAnnotation,
        .annotationLayer .lineAnnotation svg line,
        .annotationLayer .squareAnnotation svg rect,
        .annotationLayer .circleAnnotation svg ellipse,
        .annotationLayer .polylineAnnotation svg polyline,
        .annotationLayer .polygonAnnotation svg polygon,
        .annotationLayer .caretAnnotation,
        .annotationLayer .inkAnnotation svg polyline,
        .annotationLayer .stampAnnotation,
        .annotationLayer .fileAttachmentAnnotation {
            cursor: pointer;
        }

        .annotationLayer section svg {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .annotationLayer .annotationTextContent {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            color: transparent;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
            pointer-events: none;
        }

        .annotationLayer .annotationTextContent span {
            width: 100%;
            display: inline-block;
        }


        :root {
            --xfa-unfocused-field-background: url("data:image/svg+xml;charset=UTF-8,<svg width='1px' height='1px' xmlns='http://www.w3.org/2000/svg'><rect width='100%' height='100%' style='fill:rgba(0, 54, 255, 0.13);'/></svg>");
            --xfa-focus-outline: auto;
        }

        @media (forced-colors: active) {
            :root {
                --xfa-focus-outline: 2px solid CanvasText;
            }

            .xfaLayer *:required {
                outline: 1.5px solid selectedItem;
            }
        }

        .xfaLayer {
            background-color: transparent;
        }

        .xfaLayer .highlight {
            margin: -1px;
            padding: 1px;
            background-color: rgba(239, 203, 237, 1);
            border-radius: 4px;
        }

        .xfaLayer .highlight.appended {
            position: initial;
        }

        .xfaLayer .highlight.begin {
            border-radius: 4px 0 0 4px;
        }

        .xfaLayer .highlight.end {
            border-radius: 0 4px 4px 0;
        }

        .xfaLayer .highlight.middle {
            border-radius: 0;
        }

        .xfaLayer .highlight.selected {
            background-color: rgba(203, 223, 203, 1);
        }

        .xfaPage {
            overflow: hidden;
            position: relative;
        }

        .xfaContentarea {
            position: absolute;
        }

        .xfaPrintOnly {
            display: none;
        }

        .xfaLayer {
            position: absolute;
            text-align: initial;
            top: 0;
            left: 0;
            transform-origin: 0 0;
            line-height: 1.2;
        }

        .xfaLayer * {
            color: inherit;
            font: inherit;
            font-style: inherit;
            font-weight: inherit;
            font-kerning: inherit;
            letter-spacing: -0.01px;
            text-align: inherit;
            text-decoration: inherit;
            box-sizing: border-box;
            background-color: transparent;
            padding: 0;
            margin: 0;
            pointer-events: auto;
            line-height: inherit;
        }

        .xfaLayer *:required {
            outline: 1.5px solid red;
        }

        .xfaLayer div {
            pointer-events: none;
        }

        .xfaLayer svg {
            pointer-events: none;
        }

        .xfaLayer svg * {
            pointer-events: none;
        }

        .xfaLayer a {
            color: blue;
        }

        .xfaRich li {
            margin-left: 3em;
        }

        .xfaFont {
            color: black;
            font-weight: normal;
            font-kerning: none;
            font-size: 10px;
            font-style: normal;
            letter-spacing: 0;
            text-decoration: none;
            vertical-align: 0;
        }

        .xfaCaption {
            overflow: hidden;
            flex: 0 0 auto;
        }

        .xfaCaptionForCheckButton {
            overflow: hidden;
            flex: 1 1 auto;
        }

        .xfaLabel {
            height: 100%;
            width: 100%;
        }

        .xfaLeft {
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .xfaRight {
            display: flex;
            flex-direction: row-reverse;
            align-items: center;
        }

        .xfaLeft>.xfaCaption,
        .xfaLeft>.xfaCaptionForCheckButton,
        .xfaRight>.xfaCaption,
        .xfaRight>.xfaCaptionForCheckButton {
            max-height: 100%;
        }

        .xfaTop {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .xfaBottom {
            display: flex;
            flex-direction: column-reverse;
            align-items: flex-start;
        }

        .xfaTop>.xfaCaption,
        .xfaTop>.xfaCaptionForCheckButton,
        .xfaBottom>.xfaCaption,
        .xfaBottom>.xfaCaptionForCheckButton {
            width: 100%;
        }

        .xfaBorder {
            background-color: transparent;
            position: absolute;
            pointer-events: none;
        }

        .xfaWrapped {
            width: 100%;
            height: 100%;
        }

        .xfaTextfield:focus,
        .xfaSelect:focus {
            background-image: none;
            background-color: transparent;
            outline: var(--xfa-focus-outline);
            outline-offset: -1px;
        }

        .xfaCheckbox:focus,
        .xfaRadio:focus {
            outline: var(--xfa-focus-outline);
        }

        .xfaTextfield,
        .xfaSelect {
            height: 100%;
            width: 100%;
            flex: 1 1 auto;
            border: none;
            resize: none;
            background-image: var(--xfa-unfocused-field-background);
        }

        .xfaSelect {
            padding-left: 2px;
            padding-right: 2px;
            padding-inline: 2px;
        }

        .xfaTop>.xfaTextfield,
        .xfaTop>.xfaSelect,
        .xfaBottom>.xfaTextfield,
        .xfaBottom>.xfaSelect {
            flex: 0 1 auto;
        }

        .xfaButton {
            cursor: pointer;
            width: 100%;
            height: 100%;
            border: none;
            text-align: center;
        }

        .xfaLink {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        .xfaCheckbox,
        .xfaRadio {
            width: 100%;
            height: 100%;
            flex: 0 0 auto;
            border: none;
        }

        .xfaRich {
            white-space: pre-wrap;
            width: 100%;
            height: 100%;
        }

        .xfaImage {
            -o-object-position: left top;
            object-position: left top;
            -o-object-fit: contain;
            object-fit: contain;
            width: 100%;
            height: 100%;
        }

        .xfaLrTb,
        .xfaRlTb,
        .xfaTb {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .xfaLr {
            display: flex;
            flex-direction: row;
            align-items: stretch;
        }

        .xfaRl {
            display: flex;
            flex-direction: row-reverse;
            align-items: stretch;
        }

        .xfaTb>div {
            justify-content: left;
        }

        .xfaPosition {
            position: relative;
        }

        .xfaArea {
            position: relative;
        }

        .xfaValignMiddle {
            display: flex;
            align-items: center;
        }

        .xfaTable {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .xfaTable .xfaRow {
            display: flex;
            flex-direction: row;
            align-items: stretch;
        }

        .xfaTable .xfaRlRow {
            display: flex;
            flex-direction: row-reverse;
            align-items: stretch;
            flex: 1;
        }

        .xfaTable .xfaRlRow>div {
            flex: 1;
        }

        .xfaNonInteractive input,
        .xfaNonInteractive textarea,
        .xfaDisabled input,
        .xfaDisabled textarea,
        .xfaReadOnly input,
        .xfaReadOnly textarea {
            background: initial;
        }

        @media print {

            .xfaTextfield,
            .xfaSelect {
                background: transparent;
            }

            .xfaSelect {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                text-indent: 1px;
                text-overflow: "";
            }
        }


        :root {
            --focus-outline: solid 2px blue;
            --hover-outline: dashed 2px blue;
            --freetext-line-height: 1.35;
            --freetext-padding: 2px;
            --editorFreeText-editing-cursor: text;
            --editorInk-editing-cursor: pointer;
        }

        @media (-webkit-min-device-pixel-ratio: 1.1),
        (min-resolution: 1.1dppx) {
            :root {}
        }

        @media (forced-colors: active) {
            :root {
                --focus-outline: solid 3px ButtonText;
                --hover-outline: dashed 3px ButtonText;
            }
        }

        [data-editor-rotation="90"] {
            transform: rotate(90deg);
        }

        [data-editor-rotation="180"] {
            transform: rotate(180deg);
        }

        [data-editor-rotation="270"] {
            transform: rotate(270deg);
        }

        .annotationEditorLayer {
            background: transparent;
            position: absolute;
            top: 0;
            left: 0;
            font-size: calc(100px * var(--scale-factor));
            transform-origin: 0 0;
            cursor: auto;
            z-index: 4;
        }

        .annotationEditorLayer.freeTextEditing {
            cursor: var(--editorFreeText-editing-cursor);
        }

        .annotationEditorLayer.inkEditing {
            cursor: var(--editorInk-editing-cursor);
        }

        .annotationEditorLayer .selectedEditor {
            outline: var(--focus-outline);
            resize: none;
        }

        .annotationEditorLayer .freeTextEditor {
            position: absolute;
            background: transparent;
            border-radius: 3px;
            padding: calc(var(--freetext-padding) * var(--scale-factor));
            resize: none;
            width: auto;
            height: auto;
            z-index: 1;
            transform-origin: 0 0;
            touch-action: none;
            cursor: auto;
        }

        .annotationEditorLayer .freeTextEditor .internal {
            background: transparent;
            border: none;
            top: 0;
            left: 0;
            overflow: visible;
            white-space: nowrap;
            resize: none;
            font: 10px sans-serif;
            line-height: var(--freetext-line-height);
        }

        .annotationEditorLayer .freeTextEditor .overlay {
            position: absolute;
            display: none;
            background: transparent;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .annotationEditorLayer .freeTextEditor .overlay.enabled {
            display: block;
        }

        .annotationEditorLayer .freeTextEditor .internal:empty::before {
            content: attr(default-content);
            color: gray;
        }

        .annotationEditorLayer .freeTextEditor .internal:focus {
            outline: none;
        }

        .annotationEditorLayer .inkEditor.disabled {
            resize: none;
        }

        .annotationEditorLayer .inkEditor.disabled.selectedEditor {
            resize: horizontal;
        }

        .annotationEditorLayer .freeTextEditor:hover:not(.selectedEditor),
        .annotationEditorLayer .inkEditor:hover:not(.selectedEditor) {
            outline: var(--hover-outline);
        }

        .annotationEditorLayer .inkEditor {
            position: absolute;
            background: transparent;
            border-radius: 3px;
            overflow: auto;
            width: 100%;
            height: 100%;
            z-index: 1;
            transform-origin: 0 0;
            cursor: auto;
        }

        .annotationEditorLayer .inkEditor.editing {
            resize: none;
            cursor: inherit;
        }

        .annotationEditorLayer .inkEditor .inkEditorCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            touch-action: none;
        }

        :root {
            --viewer-container-height: 0;
            --pdfViewer-padding-bottom: 0;
            --page-margin: 1px auto -8px;
            --page-border: 9px solid transparent;
            --spreadHorizontalWrapped-margin-LR: -3.5px;
        }

        @media screen and (forced-colors: active) {
            :root {
                --pdfViewer-padding-bottom: 9px;
                --page-margin: 8px auto -1px;
                --page-border: 1px solid CanvasText;
                --page-border-image: none;
                --spreadHorizontalWrapped-margin-LR: 3.5px;
            }
        }

        [data-main-rotation="90"] {
            transform: rotate(90deg) translateY(-100%);
        }

        [data-main-rotation="180"] {
            transform: rotate(180deg) translate(-100%, -100%);
        }

        [data-main-rotation="270"] {
            transform: rotate(270deg) translateX(-100%);
        }

        .pdfViewer {
            background-color: #d7d7d7;
            padding-bottom: var(--pdfViewer-padding-bottom);
        }

        .pdfViewer .canvasWrapper {
            overflow: hidden;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .pdfViewer .page {
            direction: ltr;
            width: 816px;
            height: 1056px;
            margin: var(--page-margin);
            position: relative;
            overflow: visible;
            border: var(--page-border);
            -o-border-image: var(--page-border-image);
            border-image: var(--page-border-image);
            background-clip: content-box;
            background-color: rgba(255, 255, 255, 1);
        }

        .pdfViewer .dummyPage {
            position: relative;
            width: 0;
            height: var(--viewer-container-height);
        }

        .pdfViewer.removePageBorders .page {
            margin: 0 auto 10px;
            border: none;
        }

        .pdfViewer.singlePageView {
            display: inline-block;
        }

        .pdfViewer.singlePageView .page {
            margin: 0;
            border: none;
        }

        .pdfViewer.scrollHorizontal,
        .pdfViewer.scrollWrapped,
        .spread {
            margin-left: 3.5px;
            margin-right: 3.5px;
            text-align: center;
        }

        .pdfViewer.scrollHorizontal,
        .spread {
            white-space: nowrap;
        }

        .pdfViewer.removePageBorders,
        .pdfViewer.scrollHorizontal .spread,
        .pdfViewer.scrollWrapped .spread {
            margin-left: 0;
            margin-right: 0;
        }

        .spread .page,
        .spread .dummyPage,
        .pdfViewer.scrollHorizontal .page,
        .pdfViewer.scrollWrapped .page,
        .pdfViewer.scrollHorizontal .spread,
        .pdfViewer.scrollWrapped .spread {
            display: inline-block;
            vertical-align: middle;
        }

        .spread .page,
        .pdfViewer.scrollHorizontal .page,
        .pdfViewer.scrollWrapped .page {
            margin-left: var(--spreadHorizontalWrapped-margin-LR);
            margin-right: var(--spreadHorizontalWrapped-margin-LR);
        }

        .pdfViewer.removePageBorders .spread .page,
        .pdfViewer.removePageBorders.scrollHorizontal .page,
        .pdfViewer.removePageBorders.scrollWrapped .page {
            margin-left: 5px;
            margin-right: 5px;
        }

        .pdfViewer .page canvas {
            margin: 0;
            display: block;
        }

        .pdfViewer .page canvas[hidden] {
            display: none;
        }

        .pdfViewer .page canvas[zooming] {
            width: 100%;
            height: 100%;
        }

        .pdfViewer .page .loadingIcon {
            position: absolute;
            display: block;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            z-index: 5;
        }

        .pdfViewer .page .loadingIcon.notVisible {
            background: none;
        }

        .pdfViewer.enablePermissions .textLayer span {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            user-select: none !important;
            cursor: not-allowed;
        }

        .pdfPresentationMode .pdfViewer {
            padding-bottom: 0;
        }

        .pdfPresentationMode .spread {
            margin: 0;
        }

        .pdfPresentationMode .pdfViewer .page {
            margin: 0 auto;
            border: 2px solid transparent;
        }
    </style>
</head>

<body>
    <dilass="pdfViewer">
        <div class="page" style="height: 842.25px; width: 595.5px;">
            <div class="canvasLayer"><img src="images/background-pdf.png"></div>
            <div class="textLayer">
                <span role="presentation" dir="ltr" style="left: 257.76px; top: 102.281px; font-size: 12.0045px; font-family: sans-serif; transform: scaleX(1.08916);">Guest Details</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 229.562px; top: 421.1px; font-size: 12.0045px; font-family: sans-serif; transform: scaleX(1.11494);">Accommodation Policies</span>
                <span role="presentation" dir="ltr" style="left: 375.316px; top: 421.1px; font-size: 12.0045px; font-family: sans-serif;">:</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 151.696px; top: 32.1581px; font-size: 21.4731px; font-family: sans-serif; transform: scaleX(1.01866);">BOOKING CONFIRMATION FORM</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 306.605px; top: 175.682px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.07262);">Date: {{ $booking->date }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 36.9737px; top: 501.782px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.07381);">Standard check-in is from 2:00 PM. Early check-ins may</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 36.9737px; top: 515.288px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.09522);">be allowed based on availability.</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 35.9781px; top: 542.143px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.06058);">A valid photo ID (e.g., CNIC, Passport) is required at</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 35.9781px; top: 555.648px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.09763);">check-in. A security deposit is required for incidentals.</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 13.2487px; top: 179.904px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.04369);">Name: {{ $booking->name }}</span> <br role="presentation"> 
                <span role="presentation" dir="ltr" style="left: 11.9265px; top: 204.73px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.0986);">Contact Number: {{ $booking->contact_number }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 11.3406px; top: 234.668px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.05154);">CNIC Number: {{ $booking->cnic_number }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 11.9265px; top: 301.093px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.04704);">Check In Date: {{ $booking->check_in_date }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 11.9265px; top: 329.825px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.05656);">Check Out Date: {{ $booking->check_out_date }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 11.9265px; top: 357.235px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.09055);">Number of Nights: {{ $booking->number_of_nights }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 308.938px; top: 330.025px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.09437);">Price per night: {{ $booking->price_per_night }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 349.816px; top: 639.995px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.09278);">Respectful Environment: No parties or</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 349.816px; top: 653.5px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.12266);">events permitted.</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 306.628px; top: 358.885px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.11533);">Total Amount: {{ $booking->total_amount }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 311.149px; top: 300.389px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.07329);">Approach Via: {{ $booking->approach_via }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 305.633px; top: 203.738px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.07909);">Number of Guest: {{ $booking->number_of_guest }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 304.024px; top: 240.152px; font-size: 13.0024px; font-family: sans-serif; transform: scaleX(1.06908);">Room Number: {{ $booking->room->room_number }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 403.787px; top: 471.799px; font-size: 15.0057px; font-family: sans-serif; transform: scaleX(1.06864);">Check Out:</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 159.959px; top: 271.867px; font-size: 14.5855px; font-family: sans-serif; transform: scaleX(1.0246);">BOOKING AND PAYMENT DETAILS:</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 35.3803px; top: 579.337px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.09677);">Provide your reservation number or booking</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 35.3803px; top: 592.842px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.11122);">confirmation at check-in.</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 351.773px; top: 499.692px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.07402);">Standard check-out is by 12:00 PM. Late</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 351.773px; top: 513.197px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.07212);">check-outs may be arranged based on</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 351.773px; top: 526.702px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.12497);">room availability</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 348.537px; top: 548.71px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.07558);">Guests are advised to check the room</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 348.537px; top: 562.216px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.10724);">for personal items before departure.</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 360.044px; top: 783.482px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.0436);">Name: {{ $booking->name }}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 315.876px; top: 766.601px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.07653);">Agreed:</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 39.7579px; top: 823.247px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.06653);">{{$settings['instagram_link']}}</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 31.5479px; top: 696.261px; font-size: 12.0045px; font-family: sans-serif; transform: scaleX(1.09536);">Thank you for choosing us! We hope you have a great stay and a wonderful experience.</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 349.816px; top: 587.975px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.07809);">Rooms will be inspected for damages or</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 349.816px; top: 601.48px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.10004);">missing items after check-out. Additional</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 349.816px; top: 614.985px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.067);">charges may apply for damages.</span>
                <span role="presentation" dir="ltr" style="left: 35.9781px; top: 610.991px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.0825);">Pets are not allowed in designated rooms</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 85.51px; top: 469.549px; font-size: 15.0057px; font-family: sans-serif; transform: scaleX(1.06143);">Check In:</span><br role="presentation">
                <span role="presentation" dir="ltr" style="left: 261.397px; top: 822.747px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.05929);">{{$settings['phone']}}</span>
                <span role="presentation" dir="ltr" style="left: 323.253px; top: 822.747px; font-size: 10.0013px; font-family: sans-serif;"> </span>
                <span role="presentation" dir="ltr" style="left: 506.066px; top: 819.872px; font-size: 10.0013px; font-family: sans-serif; transform: scaleX(1.04733);">{{$settings['facebook_link']}}</span>
            </div>
        </div>
    </div>
</body>

</html>