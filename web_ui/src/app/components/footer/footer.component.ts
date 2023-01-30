import { Component, OnInit } from '@angular/core';
import { timer } from 'rxjs';

@Component({
    selector: 'app-footer',
    templateUrl: './footer.component.html',
    styleUrls: ['./footer.component.scss']
})

export class FooterComponent implements OnInit {

    copyright_date: number = 2023;
    website_url: string = "Warehouse.com";

    street: string = "3 ABC Street";
    suburb: string = "Industria";
    city: string = "Industrial City"
    postal_code: number = 1415;
    tel_number: string = "014 159 2653";

    date_time!: Date;

    ngOnInit() {

        timer( 0, 1000 ).subscribe(
            () => { this.date_time = new Date() }
        );

    }

}
